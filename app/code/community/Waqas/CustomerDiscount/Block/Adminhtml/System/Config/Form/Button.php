<?php 

class Waqas_CustomerDiscount_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('customerdiscount/system/config/button/button.phtml');
    }
 
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
 
    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_customerdiscount/deleterules');
    }
 
    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $buttonTitle = 'Start Promotion';
        if(Mage::getStoreConfig('waqas/ext_config_grouop/ext_select',Mage::app()->getStore())){
            $buttonTitle = 'Stop Promotion';                      
        }
        

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'customerdiscount_button',
            'label'     => $this->helper('adminhtml')->__($buttonTitle),
            'onclick'   => 'javascript:check(); return false;'
        ));
 
        return $button->toHtml();
    }
}