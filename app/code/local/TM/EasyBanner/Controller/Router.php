<?php

class TM_EasyBanner_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();

        $easybanner = new TM_EasyBanner_Controller_Router();
        $front->addRouter('easybanner', $easybanner);
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
        $parts = explode('/', $identifier);

        if (strpos($identifier, 'click') !== 0 || count($parts) < 3 || $parts[1] != 'id') {
            return false;
        }

        if (!Mage::getModel('easybanner/banner')->load($parts[2])
                ->check(Mage::app()->getStore()->getId())) {

            return false;
        }

        $request->setModuleName('easybanner')
            ->setControllerName('index')
            ->setActionName('click')
            ->setParam('banner_id', $parts[2]);
        $request->setAlias(
            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
            $identifier
        );
        return true;
    }
}