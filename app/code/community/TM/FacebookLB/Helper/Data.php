<?php

class TM_FacebookLB_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCategoryLikeButton($product)
    {
        $result = '';
        $productUrl = $product->getProductUrl();

        if (Mage::getStoreConfig('facebooklb/category_products/enabled')) {
            $send = 'false';
            if (Mage::getStoreConfig('facebooklb/category_products/send')) {
                $send = 'true';
            }
            $showFaces = 'false';
            if (Mage::getStoreConfig('facebooklb/category_products/showfaces')) {
                $showFaces = 'true';
            }
            $layout = Mage::getStoreConfig('facebooklb/category_products/layout');
            $result .= '
                <div
                    class           ="fb-like"
                    data-href       ="' . $productUrl . '"
                    data-layout     ="' . $layout . '"
                    data-show-faces ="' . $showFaces .'"
                    data-share      ="' . $send . '"
                </div>
            ';
        }
        return $result;
    }
}
