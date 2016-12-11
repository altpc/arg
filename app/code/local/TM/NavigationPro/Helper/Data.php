<?php

class TM_NavigationPro_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function loadCachedMenuTree($name)
    {
        $cacheKey = implode('|', array(
            'navigationpro-menu',
            $name,
            Mage::app()->getStore()->getId()
        ));
        $cacheKey = md5($cacheKey);

        if (!$tree = $this->_loadTree($cacheKey)) {
            $tree = Mage::getResourceModel('navigationpro/menu')
                ->loadTreeByName($name);

            $this->_saveTree($tree, $cacheKey);
        }
        return $tree;
    }

    protected function _saveTree($tree, $cacheKey)
    {
        $innerObjects = array('columns', 'siblings');
        $treeData = array();
        foreach ($tree as $menu) {
            $data = array();
            foreach ($menu->getData() as $key => $value) {
                if (in_array($key, $innerObjects)) {
                    continue;
                }
                $data[$key] = $value;
            }

            foreach ($innerObjects as $name) {
                $data[$name] = array();
                $objects = $menu->getData($name);
                if (empty($objects)) {
                    continue;
                }
                foreach ($objects as $object) {
                    $data[$name][] = $object->getData();
                }
            }

            $treeData[] = $data;
        }

        Mage::app()->saveCache(
            serialize($treeData),
            $cacheKey,
            array(TM_NavigationPro_Model_Menu::CACHE_TAG)
        );
    }

    protected function _loadTree($cacheKey)
    {
        $treeData = unserialize(Mage::app()->loadCache($cacheKey));
        if (!$treeData) {
            return false;
        }
        $innerObjects = array('columns', 'siblings');

        $collection = new TM_NavigationPro_Model_Resource_Menu_Collection();
        foreach ($treeData as $menuData) {
            $data = array();
            foreach ($menuData as $key => $value) {
                if (in_array($key, $innerObjects)) {
                    continue;
                }
                $data[$key] = $value;
            }

            foreach ($innerObjects as $name) {
                $data[$name] = array();
                $objects = $menuData[$name];
                if (empty($objects)) {
                    continue;
                }
                foreach ($objects as $objectData) {
                    switch ($name) {
                        case 'siblings':
                            $object = new TM_NavigationPro_Model_Sibling();
                            break;
                        case 'columns':
                            $object = new TM_NavigationPro_Model_Column();
                            break;
                    }
                    $data[$name][] = $object->addData($objectData);
                }
            }

            $menu = new TM_NavigationPro_Model_Menu();
            $menu->addData($data);
            $collection->addItem($menu);
        }
        $collection->setIsLoaded(true);

        return $collection;
    }
}
