<?php

class TM_ProLabels_Model_Observer
{
    public function autoReindexLabels($observer)
    {
        if (Mage::getStoreConfig("prolabels/general/cron")) {
            $model = Mage::getResourceModel('prolabels/label');
            $model->deleteAllLabelIndex();
            $model->applyAll();
        }
        return $this;
    }

    public function loadCatalogLabels($observer)
    {
        $productCollection = $observer->getCollection();
        $ids = array();
        foreach($productCollection as $item) {
            $ids[] = $item->getId();
        }

        $labelModel = Mage::getModel('prolabels/label');
        $labelsData = $labelModel->getCatalogLabels($ids, 'category');

        Mage::unregister('tm_product_catalog_labels');
        Mage::register('tm_product_catalog_labels', $labelsData);

        return $this;
    }


    public function loadProductLabels($observer)
    {
        $productId = $observer->getProduct()->getEntityId();

        $ids = array();
        $ids[] = $productId;
        $labelModel = Mage::getModel('prolabels/label');
        $labelsData = $labelModel->getCatalogLabels($ids, 'product');

        Mage::unregister('tm_product_page_labels');
        Mage::register('tm_product_page_labels', $labelsData);

        return $this;
    }
}
