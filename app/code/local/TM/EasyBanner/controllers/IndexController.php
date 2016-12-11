<?php

class TM_EasyBanner_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Banner click action
     */
    public function clickAction()
    {
        $bannerId = $this->getRequest()->getParam('banner_id', false);
        $model = Mage::getModel('easybanner/banner')->load($bannerId);
        if (!$bannerId || !$model->check(Mage::app()->getStore()->getId())) {
            $this->_forward('noRoute');
        } else {
            $statRes = Mage::getResourceModel('easybanner/banner_statistic')
                ->incrementClicksCount($bannerId);

            $redirectUrl = $model->getUrl();

            if (strpos($redirectUrl, 'www.') === 0) {
                $redirectUrl = 'http://' . $model->getUrl();
            } elseif (strpos($redirectUrl, 'http://') !== 0
                && strpos($redirectUrl, 'https://') !== 0) {

                $redirectUrl = Mage::getUrl($redirectUrl);
            }

            $this->getResponse()->setRedirect($redirectUrl);
        }
    }
}