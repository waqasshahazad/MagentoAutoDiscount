<?php

class Waqas_CustomerDiscount_Model_Validator extends Mage_SalesRule_Model_Validator
{
    
    protected function _canProcessRule($rule, $address)
    {
        if(!$rule->getCustomerId()){

            return parent::_canProcessRule($rule, $address);
        }
        $customerId = $address->getQuote()->getCustomerId();
        $allowCustomerId = $rule->getCustomerId();       
        if($customerId == $allowCustomerId){            
            return parent::_canProcessRule($rule, $address);            
        }
        else{
            $address->getQuote()->setCouponCode(NULL);
            $rule->setIsValidForAddress($address, false);
            return false;
        }

    }
}