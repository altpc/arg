<?php
class TM_SoldTogether_Block_Adminhtml_Order_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'soldtogether';
        $this->_controller = 'adminhtml_order';

        $this->_updateButton('save', 'label', Mage::helper('soldtogether')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('soldtogether')->__('Delete Item'));
    }

    public function getHeaderText()
    {
        if( Mage::registry('soldtogether_data') && Mage::registry('soldtogether_data')->getId() ) {
            return Mage::helper('soldtogether')->__("Edit Item");
        } else {
            return Mage::helper('soldtogether')->__('Add Item');
        }
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current' => true
        ));
    }
}