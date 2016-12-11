<?php

class TM_Attributepages_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer)
    {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();
        $front->addRouter('attributepages', $this);
    }

    /**
     * Validate and Match Page and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        $pathInfo = trim($request->getPathInfo(), '/');
        $pathParts = explode('/', $pathInfo);
        $identifiers = array();
        foreach ($pathParts as $i => $param) {
            $identifiers[] = urldecode($param);
            if ($i >= 1) {
                break;
            }
        }

        $page = Mage::helper('attributepages/page_view')->initPagesInRegistry(
            isset($identifiers[1]) ? $identifiers[1] : $identifiers[0], // current_page
            isset($identifiers[1]) ? $identifiers[0] : false,           // parent_page
            'identifier'
        );

        if (!$page) {
            return false;
        }

        $request->setModuleName('attributepages')
            ->setControllerName('page')
            ->setActionName('view')
            ->setParam('id', $page->getId());

        if ($parent = Mage::registry('attributepages_parent_page')) {
            $request->setParam('parent_id', $parent->getId());
        }

        $request->setAlias(
            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
            $pathInfo
        );

        return true;
    }
}
