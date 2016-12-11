<?php
class TM_QtySwitcher_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ENABLE = 'tm_qtyswitcher/main/enabled';

    /**
     *
     * @return json string | false
     */
    public function getJsonConfig()
    {
        $enable = (bool) Mage::getStoreConfig(self::ENABLE);
        if (!$enable) {
            return false;
        }
        $product = Mage::registry('current_product');
        if (!$product) {
            return false;
        }
        $stockItem = $product->getStockItem();
        $max = $stockItem->getMaxSaleQty();
        if ('configurable' !== $stockItem->getTypeId()) {
            $_max = (float) $stockItem->getQty();
            if ($max > $_max) {
                $max = $_max;
            }
        }
        $step = $stockItem->getQtyIncrements() ? $stockItem->getQtyIncrements() : 1;
        $config = array(
            'min' => (float) $stockItem->getMinSaleQty(),
            'max' => $max ? $max : 1000000,
            'increment' => $step
        );
        $config = json_encode($config);
        // $_config = json_encode($stockItem->getData());
        return $config;
    }
}
