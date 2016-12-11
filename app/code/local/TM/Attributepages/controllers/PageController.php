<?php

class TM_Attributepages_PageController extends Mage_Core_Controller_Front_Action
{
    protected function _initPage()
    {
        if (!$page = Mage::registry('attributepages_current_page')) {
            // links with rewrite disabled: attributepages/page/view/id/10/parent_id/1/
            $page = Mage::helper('attributepages/page_view')->initPagesInRegistry(
                (int) $this->getRequest()->getParam('id', false),
                (int) $this->getRequest()->getParam('parent_id', false),
                'entity_id'
            );
        }
        return $page;
    }

    public function viewAction()
    {
        if (!$page = $this->_initPage()) {
            return $this->_forward('noRoute');
        }

        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->addHandle('default')
            ->addHandle('ATTRIBUTEPAGES_PAGE_' . $page->getId());
        $this->addActionLayoutHandles();

        if ($page->isAttributeBasedPage()) {
            $update->addHandle('attributepages_attribute_page');
        } else {
            $update->addHandle('attributepages_option_page');

            if (Mage::helper('attributepages')->canUseLayeredNavigation()) {
                $update->addHandle('attributepages_option_page_layered');
            } else {
                $update->addHandle('attributepages_option_page_default');
            }
        }

        if ($handle = $page->getRootTemplate()) {
            $layout->helper('page/layout')->applyHandle($handle);
        }

        $this->loadLayoutUpdates();
        $update->addUpdate($page->getLayoutUpdateXml());
        $this->generateLayoutXml()->generateLayoutBlocks();

        if ($root = $layout->getBlock('root')) {
            if ($page->isAttributeBasedPage()) {
                $suffix = '-attribute-page';
            } else {
                $suffix = '-option-page';
            }
            $root->addBodyClass('attributepages-' . $suffix);
            $root->addBodyClass('attributepages-' . $page->getIdentifier());
        }

        if ($breadcrumbs = $layout->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label' => Mage::helper('cms')->__('Home'),
                'title' => Mage::helper('cms')->__('Go to Home Page'),
                'link'  => Mage::getBaseUrl()
            ));
            if ($parentPage = Mage::registry('attributepages_parent_page')) {
                $breadcrumbs->addCrumb('parent_page', array(
                    'label' => $parentPage->getPageTitle(),
                    'title' => $parentPage->getPageTitle(),
                    'link'  => Mage::getUrl($parentPage->getIdentifier())
                ));
            }
            $breadcrumbs->addCrumb('current_page', array(
                'label' => $page->getPageTitle(),
                'title' => $page->getPageTitle()
            ));
        }

        if ($headBlock = $layout->getBlock('head')) {
            if ($title = $page->getPageTitle()) {
                $headBlock->setTitle($title);
            }
            if ($description = $page->getMetaDescription()) {
                $headBlock->setDescription($description);
            }
            if ($keywords = $page->getMetaKeywords()) {
                $headBlock->setKeywords($keywords);
            }
            if (Mage::helper('catalog/category')->canUseCanonicalTag()) {
                if ($category = Mage::registry('current_category')) {
                    $headBlock->removeItem('link_rel', $category->getUrl());
                }
                $headBlock->addLinkRel('canonical', $page->getUrl());
            }
        }

        if ($page->isOptionBasedPage()) {
            Mage::helper('attributepages/page_view')
                ->initCollectionFilters($page, $this);
        }

        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }
}
