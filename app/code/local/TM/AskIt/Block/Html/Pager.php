<?php
class TM_AskIt_Block_Html_Pager extends Mage_Page_Block_Html_Pager
{
    public function getPagerUrl($params = array())
    {
        $product = Mage::registry('current_product');
        if ($product) {
            $item = array(
                'item_type_id' => TM_AskIt_Model_Item_Type::PRODUCT_ID,
                'item_id' => $product->getId()
            );
            $urlParams = array();
            // $urlParams['_current']  = true;
            // $urlParams['_escape']   = true;
            // $urlParams['_use_rewrite']   = true;
            $urlParams['_query']    = $params;
            return Mage::helper('askit')->getAskitActionHref($item, $urlParams);
        }
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }
}
