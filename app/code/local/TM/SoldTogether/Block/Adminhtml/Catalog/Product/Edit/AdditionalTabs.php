<?php

class TM_SoldTogether_Block_Adminhtml_Catalog_Product_Edit_AdditionalTabs extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareLayout()
    {
        $product = Mage::registry('product');
        if ($product->getId()) {
            $this->getLayout()
                ->getBlock('product_tabs')
                ->addTab('soldtogether_order', array(
                    'label'     => Mage::helper('catalog')->__('Frequently Bought Together'),
                    'url'       => $this->getUrl('adminhtml/soldtogether_order/related', array('_current' => true)),
                    'class'     => 'ajax'
                ));
            $this->getLayout()
                ->getBlock('product_tabs')
                ->addTab('soldtogether_customer', array(
                    'label'     => Mage::helper('catalog')->__('Customers Who Bought This Item Also Bought'),
                    'url'       => $this->getUrl('adminhtml/soldtogether_customer/related', array('_current' => true)),
                    'class'     => 'ajax'
                ));
        }

        return parent::_prepareLayout();
    }
}
