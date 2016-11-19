<?php
class Waqas_CustomerDiscount_Model_Observer
{
	protected $promotionOn = false;
	protected $discountOnShipping = false;
	protected $discountInput = 0;
	protected $purchasedInput = 0;

	public function __construct()
    {          	
        $this->promotionOn = Mage::getStoreConfig('waqas/ext_config_grouop/ext_select',Mage::app()->getStore());
        $this->discountInput = Mage::getStoreConfig('waqas/ext_setting_group/discount_input',Mage::app()->getStore());
        $this->purchasedInput = Mage::getStoreConfig('waqas/ext_setting_group/purchased_input',Mage::app()->getStore());
        $this->discountOnShipping = Mage::getStoreConfig('waqas/ext_setting_group/shipping_select',Mage::app()->getStore());	

    }
	
	public function CustomerAutoDiscount(Varien_Event_Observer $observer)
	{			
		$sessionCustomer = Mage::getSingleton("customer/session");
		if($this->promotionOn && $sessionCustomer->isLoggedIn()){//If Extention is enabled and customer is logged in
			$quote = $observer->getEvent()->getQuote();
			if($quote->getItemsCount())	{
				//If a customer has achieved amount set in extension settings in lifetime sales
				if($this->purchasedInput <= $this->getCustomerLifeTimeShopping($sessionCustomer)){
					$ruleId = $this->getAlreadyExistedDiscountRule();					
					if(!$ruleId){						
						$this->createdDiscoutRule();
					}					
							            
				}//End of If a customer has achieved amount set in extension settings in lifetime sales 

			}	
			

		}//End of If Extention is enabled and customer is logged 
	}

	public function createdDiscoutRule()
	{		
		$ruleData = $this->prepairDataForRule();
		$model = Mage::getModel('salesrule/rule');
		$data = $this->_filterDates($ruleData, array('from_date', 'to_date'));		 
		$validateResult = $model->validateData(new Varien_Object($data));
		 
		if ($validateResult == true) {
		 
		    if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
		            && isset($data['discount_amount'])) {
		        $data['discount_amount'] = min(100, $data['discount_amount']);
		    }
		 
		    if (isset($data['rule']['conditions'])) {
		        $data['conditions'] = $data['rule']['conditions'];
		    }
		 
		    if (isset($data['rule']['actions'])) {
		        $data['actions'] = $data['rule']['actions'];
		    }
		 
		    unset($data['rule']);
		 
		    $model->loadPost($data);
		 
		    return $model->save();
		}

	}
	

	public function getAlreadyExistedDiscountRule(){
		$model = Mage::getModel('salesrule/rule')
        ->getCollection()
        ->addFieldToFilter('name', array('eq'=>sprintf('AUTO_GENERATION_%s - '.$this->discountInput.'%% Life time discount', Mage::getSingleton('customer/session')->getCustomerId())))
        ->getFirstItem(); 		
		return $model->getRuleId();
	}

	public function removeAppliedDiscoutRule()
	{		
		$model = Mage::getModel('salesrule/rule')
        ->getCollection()
        ->addFieldToFilter('name', array('eq'=>sprintf('AUTO_GENERATION_%s - '.$this->discountInput.'%% Life time discount', Mage::getSingleton('customer/session')->getCustomerId())))
        ->getFirstItem(); 
		$model->delete();

	}
	//Getting All orders of Current Customer to get all life time shopping 
	public function getCustomerLifeTimeShopping($sessionCustomer)
	{		
	//Getting Customer email to get all life time purchases
		$customerEmail = $sessionCustomer->getCustomer()->getEmail();
		$customerLifeTimeShoopingAmount = 0;
		$orders = Mage::getModel('sales/order')->getCollection()
     	->addAttributeToFilter('customer_email', $customerEmail)
     	->addFieldToFilter('status', 'complete');
     	//Getting Total from Customer orders
     	foreach ($orders as $order) {
		    $total = $order->getGrandTotal();
		     $customerLifeTimeShoopingAmount+= $total;	
		}//End of foreach gettting total from customer orders

		return $customerLifeTimeShoopingAmount;

	}

	public function prepairDataForRule()
	{
		$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		$data = array(
		    'product_ids' => null,
		    'name' => sprintf('AUTO_GENERATION_%s - '.$this->discountInput.'%% Life time discount', Mage::getSingleton('customer/session')->getCustomerId()),
		    'description' => null,
		    'is_active' => 1,
		    'website_ids' => array(1),
		    'customer_group_ids' => array(1),
		    'customer_id' => $customerId,
		    'coupon_type' => 1,
		    'coupon_code' => '',
		    'uses_per_coupon' => 1,
		    'uses_per_customer' => 1,		    
		    'from_date' => null,
		    'to_date' => null,
		    'sort_order' => null,
		    'is_rss' => 1,		    
		    'rule[conditions][1][type]' => 'salesrule/rule_condition_combine',
		    'rule[conditions][1][aggregator]' => 'all',		   
		    'rule[conditions][1][value]' => '1',		   
		    'rule[conditions][1][new_child]' => '',		   
		    'simple_action' => 'by_percent',
		    'discount_amount' => $this->discountInput,
		    'discount_qty' => 0,
		    'discount_step' => null,
		    'apply_to_shipping' => $this->discountOnShipping,
		    'simple_free_shipping' => 0,
		    'stop_rules_processing' => 0,
		    'rule[actions][1][type]' => 'salesrule/rule_condition_product_combine',
		    'rule[actions][1][aggregator]' => 'all',		   
		    'rule[actions][1][value]' => '1',		   
		    'rule[actions][1][new_child]' => '',	
		    'store_labels' => array($this->discountInput.'% Life time discount')
		);

		return $data;

	}

	protected function _filterDates($array, $dateFields)
    {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }

    
		
}
