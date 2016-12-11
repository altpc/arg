<?php

class TM_NavigationPro_Model_Observer
{
    /*=================================*/
    /* Compatibility with magento 1620 */
    /*=================================*/
    public function addCatalogToTopmenuItems(Varien_Event_Observer $observer)
    {
        if (!@class_exists('Mage_Page_Block_Html_Topmenu')) {
            $this->_addCategoriesToMenu(Mage::helper('catalog/category')->getStoreCategories(), $observer->getMenu());
        }
    }

    protected function _addCategoriesToMenu($categories, $parentCategoryNode)
    {
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $nodeId = 'category-node-' . $category->getId();

            $tree = $parentCategoryNode->getTree();
            $categoryData = array(
                'name' => $category->getName(),
                'id' => $nodeId,
                'url' => Mage::helper('catalog/category')->getCategoryUrl($category),
                'is_active' => $this->_isActiveMenuCategory($category)
            );
            $categoryNode = new Varien_Data_Tree_Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            if (Mage::helper('catalog/category_flat')->isEnabled()) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->_addCategoriesToMenu($subcategories, $categoryNode);
        }
    }

    protected function _isActiveMenuCategory($category)
    {
        $catalogLayer = Mage::getSingleton('catalog/layer');
        if (!$catalogLayer) {
            return false;
        }

        $currentCategory = $catalogLayer->getCurrentCategory();
        if (!$currentCategory) {
            return false;
        }

        $categoryPathIds = explode(',', $currentCategory->getPathInStore());
        return in_array($category->getId(), $categoryPathIds);
    }
}
