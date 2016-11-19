<?php

class Waqas_CustomerDiscount_Adminhtml_CustomerdiscountController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Return some checking result
     *
     * @return void
     */
    public function deleterulesAction()
    {
        if(Mage::getStoreConfig('waqas/ext_config_grouop/ext_select',Mage::app()->getStore())){            
            Mage::getConfig()->saveConfig('waqas/ext_config_grouop/ext_select', '0', 'default', 0); 
            Mage::getSingleton('core/session')->addError('Promotion Stoped Successfully!');

        }
        else{
            Mage::getConfig()->saveConfig('waqas/ext_config_grouop/ext_select', '1', 'default', 0);            
            Mage::getSingleton('core/session')->addSuccess('Promotion Started Successfully!');
        }

        $count = $this->deleteByCustomerIds();
        if($count){
            $result = $count.' Related salesRules deleted.';
        }
        else{
            $result = 'Promotion Started Successfully!';
        }
        
        Mage::app()->getResponse()->setBody($result);
    }

    public function deleteByCustomerIds()
    {
        $count = 0;
        $rules = Mage::getResourceModel('salesrule/rule_collection')->load();
        foreach ($rules as $code) {           
                if ($code->getCustomerId()) {                    
                    $code->delete();
                    $count++;
                }            
        }
        return $count;
    }

}