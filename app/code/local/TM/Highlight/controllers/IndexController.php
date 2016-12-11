<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $type = $this->getRequest()->getParam('type');
        $type = trim($type, '/ ');
        $typeMapping = array(
            TM_Highlight_Block_Product_New::PAGE_TYPE        => 'highlight/product_new',
            TM_Highlight_Block_Product_Special::PAGE_TYPE    => 'highlight/product_special',
            TM_Highlight_Block_Product_Featured::PAGE_TYPE   => 'highlight/product_featured',
            TM_Highlight_Block_Product_Bestseller::PAGE_TYPE => 'highlight/product_bestseller',
            TM_Highlight_Block_Product_Popular::PAGE_TYPE    => 'highlight/product_popular'
        );
        if (!isset($typeMapping[$type])) {
            return $this->_forward('noRoute');
        }

        if ($this->getRequest()->getQuery('type')) {
            $urlKey = Mage::helper('highlight')->getPageUrlKey($type);
            if ($urlKey) {
                // https://www.ltnow.com/difference-301-302-redirects-seo/
                return $this->getResponse()->setRedirect(
                    Mage::getModel('core/url')->getDirectUrl($urlKey), 301
                );
            }
        }

        $this->loadLayout();
        $layout = $this->getLayout();
        $list   = $layout->getBlock('product_list');
        $block  = $layout->createBlock($typeMapping[$type])
            ->setNameInLayout('highlight_collection');

        if (!$block || !$list) {
            return $this->_forward('noRoute');
        }

        if (method_exists($block, 'getPeriod')) {
            $block->setPeriod(Mage::getStoreConfig("highlight/pages/{$type}_period"));
        }
        $block->setTitle(Mage::getStoreConfig("highlight/pages/{$type}_title"));
        $list->setCollectionBlock($block);

        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__($list->getTitle()));
            $headBlock->setDescription(Mage::getStoreConfig("highlight/pages/{$type}_meta_description"));
            $headBlock->setKeywords(Mage::getStoreConfig("highlight/pages/{$type}_meta_keywords"));
            $headBlock->addLinkRel('canonical', $block->getPageUrl());
        }

        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }
}
