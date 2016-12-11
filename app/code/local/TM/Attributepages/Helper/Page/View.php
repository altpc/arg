<?php

class TM_Attributepages_Helper_Page_View extends Mage_Core_Helper_Abstract
{
    public function initCollectionFilters(TM_Attributepages_Model_Entity $page, $controller)
    {
        $layout = $controller->getLayout();
        $layer  = Mage::getSingleton('catalog/layer');
        if ($productList = $layout->getBlock('product_list')) {
            $productCollection = $productList->getLoadedProductCollection();
        }

        // filter by category
        $categoryId = (int) $controller->getRequest()->getParam('cat', false);
        $category = false;
        if ($categoryId) {
            $category = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($categoryId);

            if (!Mage::helper('catalog/category')->canShow($category)) {
                $category = false;
            }
        }

        if (!$category) {
            $category = $layer->getCurrentCategory();
        }
        /**
         * Hack to call for unset($this->_productLimitationFilters['category_is_anchor']);
         * in Mage/Catalog/Model/Resource/Product/Collection.php::addCategoryFilter
         * to remove cat_index.is_parent filter
         */
        $category->setIsAnchor(1);
        $productCollection->addCategoryFilter($category);

        // remove page attribute from filters
        // to prevent "You cannot define a correlation name 'ATTRIBUTE_idx' more than once"
        $layerBlockNames = Mage::getStoreConfig('attributepages/product_list/layer_block_name');
        foreach (explode(',', $layerBlockNames) as $layerBlockName) {
            $layerBlock = $layout->getBlock($layerBlockName);
            if (!$layerBlock) {
                continue;
            }
            $filterableAttributes = $layer->getFilterableAttributes();
            if ($filterableAttributes) {
                /**
                 * Previous hack causes filterable attribute recalculation, so
                 * we need to create dummy blocks for new filters to
                 * prevent error in layer/view.phtml
                 */
                foreach ($filterableAttributes as $attribute) {
                    if (!$layerBlock->getChild($attribute->getAttributeCode() . '_filter')) {
                        $layerBlock->setChild(
                            $attribute->getAttributeCode() . '_filter',
                            $layout->createBlock('core/template')
                        );
                    }
                }
                $filterableAttributes->removeItemByKey($page->getAttribute()->getAttributeId());
            }
            $layerBlock->setData('_filterable_attributes', $filterableAttributes);
        }

        /**
         * @todo get class types with reflection: php_version >= 5.3
         *  $reflectedClass = new ReflectionClass($layerBlock);
         *  $property = $reflectedClass->getProperty('_attributeFilterBlockName');
         *  $property->setAccessible(true);
         *  $property->getValue($class);
         */
        $filterType = 'catalog/layer_filter_attribute';
        $filter = Mage::getModel($filterType)
            ->setAttributeModel($page->getAttribute())
            ->setLayer($layer);
        Mage::getResourceModel($filterType)
            ->applyFilterToCollection($filter, $page->getOption()->getOptionId());
    }

    /**
     * Init current and parent pages in Mage::registry
     *
     * @param  mixed $pageId       Current page identifier or id
     * @param  mixed $parentPageId Parent page identifier or id
     * @param  string $field       identifier|entity_id
     * @return TM_Attributepages_Model_Entity|false Current page model
     */
    public function initPagesInRegistry($pageId, $parentPageId = null, $field = 'identifier')
    {
        if (!$pageId) {
            return false;
        }

        $storeId = Mage::app()->getStore()->getId();
        $collection = Mage::getResourceModel('attributepages/entity_collection')
            ->addFieldToFilter(
                $field,
                array(
                    'in' => array_filter(array($pageId, $parentPageId))
                )
            )
            ->addStoreFilter($storeId);

        // fix for the same identifiers for different options/pages
        $uniquePages = array();
        foreach ($collection as $page) {
            if (!$page->getUseForAttributePage()) {
                continue;
            }
            if (!empty($uniquePages[$page->getIdentifier()])) {
                if ($page->getStoreId() !== $storeId) {
                    continue;
                }
            }
            $uniquePages[$page->getIdentifier()] = $page;
        }

        $size = count($uniquePages);
        if (!$size) {
            return false;
        }

        $index = $size - 1;
        foreach ($uniquePages as $page) { // curent page is always last in array
            if ($page->getData($field) == $pageId) {
                $key = 'attributepages_current_page';
            } else {
                $key = 'attributepages_parent_page';
            }
            Mage::register($key, $page);
        }

        if (!$page = Mage::registry('attributepages_current_page')) {
            return false;
        }
        if ($parent = Mage::registry('attributepages_parent_page')) {
            // disallow links like brands/color or black/white or black/htc
            if ($parent->isOptionBasedPage() || $page->isAttributeBasedPage()) {
                return false;
            }
            // disallow links like color/htc or brands/white
            if ($parent->getAttributeId() !== $page->getAttributeId()) {
                return false;
            }
        }

        // disallow direct link to option page: example.com/htc
        if ($page->isOptionBasedPage()
            && !$parent
            && !Mage::getStoreConfigFlag('attributepages/seo/allow_direct_option_link')) {

            return false;
        }

        // root category is always registered as current_category
        $categoryId = Mage::app()->getStore()->getRootCategoryId();
        if ($categoryId && !Mage::registry('current_category')) {
            $category = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($categoryId);

            Mage::register('current_category', $category);
        }

        return $page;
    }
}
