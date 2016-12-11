<?php

class TM_Highlight_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();
        $front->addRouter('highlight', $this);
    }

    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        $identifier = trim($request->getPathInfo(), '/');
        $urls = Mage::helper('highlight')->getPageUrls();
        $type = array_search($identifier, $urls);
        if (!$type) {
            return false;
        }

        $request->setModuleName('highlight')
            ->setControllerName('index')
            ->setActionName('index')
            ->setParam('type', $type);
        $request->setAlias(
            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
            $identifier
        );
        return true;
    }
}