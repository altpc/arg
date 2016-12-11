<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Mysql4_System extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('prolabels/system', 'system_id');
    }

    public function load(Mage_Core_Model_Abstract $object, $value, $field=null)
    {

        if (!intval($value) && is_string($value)) {
            $field = 'identifier'; // You probably don't have an identifier...
        }
        return parent::load($object, $value, $field);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
        ->from($this->getTable('prolabels/sysstore'))
        ->where('system_id = ?', $object->getId());
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }

        return parent::_afterLoad($object);
    }

    protected function _getLoadSelect($field, $value, $object)
    {

        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $select->join(array('cbs' => $this->getTable('prolabels/sysstore')), $this->getMainTable().'.system_id = cbs.system_id')
            ->where('cbs.store_id in (0, ?) ', $object->getStoreId())
            ->order('store_id DESC')
            ->limit(1);
        }
        return $select;
    }

    public function loadLabelProductImage($object) {
        $value = $object -> getProductImage();

        $path = Mage::getBaseDir('media') . DS . 'prolabel' . DS;

        if (is_array($value) && !empty($value['delete'])) {
            @unlink($path . $value['value']);
            $object -> setProductImage('');
            return $this;
        }

        if (empty($_FILES['product_image']['name'])) {
            if (is_array($value)) {
                $object -> setProductImage($value['value']);
            }
            return $this;
        }

        try {
            $uploader = new Varien_File_Uploader('product_image');

            $uploader -> setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
            $uploader -> setAllowRenameFiles(true);
            $uploader -> save($path);
            $object -> setProductImage($uploader -> getUploadedFileName());
        } catch (Exception $e) {
            $object -> unsProductImage();
            throw $e;
            //return $this;
        }
        return $this;
    }

    public function loadLabelCategoryImage($object) {
        $value = $object -> getCategoryImage();

        $path = Mage::getBaseDir('media') . DS . 'prolabel' . DS;

        if (is_array($value) && !empty($value['delete'])) {
            @unlink($path . $value['value']);
            $object -> setCategoryImage('');
            return $this;
        }

        if (empty($_FILES['category_image']['name'])) {
            if (is_array($value)) {
                $object -> setCategoryImage($value['value']);
            }
            return $this;
        }

        try {
            $uploader = new Varien_File_Uploader('category_image');
            $uploader -> setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
            $uploader -> setAllowRenameFiles(true);
            $uploader -> save($path);
            $object -> setCategoryImage($uploader -> getUploadedFileName());
        } catch (Exception $e) {
            $object -> unsCategoryImage();
            throw $e;
            //return $this;
        }
        return $this;
    }

    public function loadLabelCategoryOutImage($object) {
        $value = $object -> getCategoryOutStockImage();

        $path = Mage::getBaseDir('media') . DS . 'prolabel' . DS;

        if (is_array($value) && !empty($value['delete'])) {
            @unlink($path . $value['value']);
            $object -> setCategoryOutStockImage('');
            return $this;
        }

        if (empty($_FILES['category_out_stock_image']['name'])) {
            if (is_array($value)) {
                $object -> setCategoryOutStockImage($value['value']);
            }
            return $this;
        }

        try {
            $uploader = new Varien_File_Uploader('category_out_stock_image');
            $uploader -> setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
            $uploader -> setAllowRenameFiles(true);
            $uploader -> save($path);
            $object -> setCategoryOutStockImage($uploader -> getUploadedFileName());
        } catch (Exception $e) {
            $object -> unsCategoryOutStockImage();
            throw $e;
            //return $this;
        }
        return $this;
    }

    public function loadLabelProductOutImage($object) {
        $value = $object -> getProductOutStockImage();

        $path = Mage::getBaseDir('media') . DS . 'prolabel' . DS;

        if (is_array($value) && !empty($value['delete'])) {
            @unlink($path . $value['value']);
            $object -> setProductOutStockImage('');
            return $this;
        }

        if (empty($_FILES['product_out_stock_image']['name'])) {
            if (is_array($value)) {
                $object -> setProductOutStockImage($value['value']);
            }
            return $this;
        }

        try {
            $uploader = new Varien_File_Uploader('product_out_stock_image');
            $uploader -> setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
            $uploader -> setAllowRenameFiles(true);
            $uploader -> save($path);
            $object -> setProductOutStockImage($uploader -> getUploadedFileName());
        } catch (Exception $e) {
            $object -> unsProductOutStockImage();
            throw $e;
            //return $this;
        }
        return $this;
    }

    public function lookupStoreIds($id)
    {
        return $this->_getReadAdapter()->fetchCol($this->_getReadAdapter()->select()
                        ->from($this->getTable('prolabels/sysstore'), 'store_id')
                        ->where("{$this->getIdFieldName()} = ?", $id)
                        );
    }

    public function getStoreIds($id)
    {
        return $this->_getReadAdapter()->fetchCol($this->_getReadAdapter()->select()
                        ->from($this->getTable('prolabels/sysstore'), 'store_id')
                        ->where("system_id = ?", $id)
                        );
    }

    /* public function getStoreSystemLabelId($rulesId) {
        $storeId = Mage::app()->getStore()->getId();

        $result = $this -> _getReadAdapter() -> fetchAll(
                        $this -> _getReadAdapter() -> select()
                        -> from($this->getTable('sysstore'))
                        ->where('rules_id = ?', $rulesId)
        );
        return $result;
    } */

    public function getSystemLabelsData($rulesId) {
        $result = $this -> _getReadAdapter() -> fetchAll(
            $this -> _getReadAdapter() -> select()
                ->from($this->getTable('system'))
                ->where('rules_id = ?', $rulesId)
                ->where('l_status = 1')
        );
        return $result;
    }

    public function getSystemContentLabels()
    {
        $result = $this->_getReadAdapter()->fetchAll(
            $this->_getReadAdapter()->select()
                ->from($this->getTable('system'))
                ->where('product_position=?','content')
                ->where('l_status = 1')
        );
        return $result;
    }
}