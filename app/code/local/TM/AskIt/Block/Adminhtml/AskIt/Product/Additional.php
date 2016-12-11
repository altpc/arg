<?php
class TM_AskIt_Block_Adminhtml_AskIt_Product_Additional extends Mage_Adminhtml_Block_Widget
{
    protected function _prepareLayout()
    {
        $product = Mage::registry('product');
        if ($product->getId()) {
            $this->getLayout()
                ->getBlock('product_tabs')
                ->addTab('askit_questions', array(
                    'label' => Mage::helper('askit')->__('AskIt Questions'),
                    'url'   => $this->getUrl('adminhtml/askIt_index/questionbyproduct', array('_current' => true)),
                    'class' => 'ajax'
                ));
        }

        return parent::_prepareLayout();
    }
}
