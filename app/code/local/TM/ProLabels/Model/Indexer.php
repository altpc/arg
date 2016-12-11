<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Indexer extends Mage_Catalog_Model_Abstract
{
    public function run()
    {
        $model = Mage::getResourceModel('prolabels/label');
        $model->deleteAllLabelIndex();
        $model->applyAll();

        return $this;
    }
}
