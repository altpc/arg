<?php

class TM_AskIt_Block_Discussion extends TM_AskIt_Block_List implements Mage_Widget_Block_Interface
{
    protected $_template = 'tm/askit/question/list.phtml';

    public function getQuestionLimit()
    {
        // if ($this->isProductViewPage()) {
        //     return Mage::getStoreConfig('askit/general/countQuestionShowOnProductPage');
        // }
        if ($count = $this->getData('count')) {
            return $count;
        }
        return parent::getQuestionLimit();
    }

    public function getProductId()
    {
        if ($product = Mage::registry('current_product')) {
            return $product->getId();
        }

        return false;
    }

    public function getProduct()
    {
        if ($product = Mage::registry('current_product')) {
            return $product;
        }
        return false;
    }

    public function getPageId()
    {
        $moduleName = $this->getRequest()->getModuleName();
        if (!in_array($moduleName, array('cms', 'askit'))) {
            return false;
        }
        $default = $this->getRequest()->getParam('id', false);
        return $this->getRequest()->getParam('page_id', $default);
    }

    public function getCategoryId()
    {
        if ($category = Mage::registry('current_category')) {
            return $category->getId();
        }

        return false;
    }

    protected function _initItem()
    {
        if ($this->getProductId()) {
            $this->_actionsParams['item_id'] = $this->getProductId();
            $this->_actionsParams['item_type_id'] = TM_AskIt_Model_Item_Type::PRODUCT_ID;
        } elseif ($this->getPageId()) {
            $this->_actionsParams['item_id'] = $this->getPageId();
            $this->_actionsParams['item_type_id'] = TM_AskIt_Model_Item_Type::CMS_PAGE_ID;
        } elseif ($this->getCategoryId()) {
            $this->_actionsParams['item_id'] = $this->getCategoryId();
            $this->_actionsParams['item_type_id'] = TM_AskIt_Model_Item_Type::PRODUCT_CATEGORY_ID;
        } else {
            throw new Mage_Exception();
        }
    }

    protected function _isCaptchaEnabled($formId = 'askit_new_question_form')
    {
        $helperClass = Mage::getConfig()->getHelperClassName('captcha');
        if (@!class_exists($helperClass)) {
            return $this;
        }
        $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
        return $captchaModel->isRequired();
    }

    protected function _beforeToHtml()
    {
        $this->_initItem();

        $newQuestionFormBlock = $this->getLayout()
            ->createBlock('core/template')
            ->setTemplate('tm/askit/question/form.phtml')
        ;
        $formId = 'askit_new_question_form';
        $isCaptchaEnabled = $this->_isCaptchaEnabled($formId);
        if ($isCaptchaEnabled) {

            $captchaBlock = $this->getLayout()->createBlock('captcha/captcha');
            if ($captchaBlock) {
                $this->getLayout()->getBlock('head')->addJs('mage/captcha.js');
                $captchaBlock
                    ->setFormId($formId)
                    ->setImgWidth(230)
                    ->setImgHeight(50)
                    ->setModuleName('TM_AskIt')
                    ;

                $newQuestionFormBlock->setChild('askit_new_question_form_captcha', $captchaBlock);
            }
        }

        $this->setChild('askit_new_question_form', $newQuestionFormBlock);

        $newAnswerFormBlock = $this->getLayout()
            ->createBlock('core/template')
            ->setTemplate('tm/askit/answer/form.phtml')
        ;
        $formId = 'askit_new_answer_form';
        $isCaptchaEnabled = $this->_isCaptchaEnabled($formId);
        if ($isCaptchaEnabled) {

            $captchaBlock = $this->getLayout()->createBlock('captcha/captcha');
            if ($captchaBlock) {
                $this->getLayout()->getBlock('head')->addJs('mage/captcha.js');
                $captchaBlock->setFormId($formId)
                    ->setImgWidth(230)
                    ->setImgHeight(50)
                    ->setModuleName('TM_AskIt')
                    ;

                $newAnswerFormBlock->setChild('askit_new_answer_form_captcha', $captchaBlock);
            }
        }

        $this->setChild('askit_new_answer_form', $newAnswerFormBlock);

        return parent::_beforeToHtml();
    }

    protected function _prepareCollection()
    {
        $params = $this->_actionsParams;
        if (isset($params['item_type_id'])) {
            $this->_collection->addItemTypeIdFilter($params['item_type_id']);
        }
        if (isset($params['item_id'])) {
            $this->_collection->addItemIdFilter($params['item_id']);
        }
//        $this->_collection
//            ->addItemTypeIdFilter($this->_actionsParams['item_type_id'])
//            ->addItemIdFilter($this->_actionsParams['item_id'])
//        ;

        return $this->_collection;
    }

    public function getAskitActionLink()
    {

        if ('askit' == $this->getRequest()->getModuleName()) {
            return;
        }
        $itemTypeId = $this->_actionsParams['item_type_id'];
        $itemId = $this->_actionsParams['item_id'];

        $_item = array('item_id' => $itemId, 'item_type_id' => $itemTypeId);

        $url = Mage::helper('askit')->getAskitActionHref($_item);
        $title = Mage::helper('askit')->__('View all related questions.');

        return '<div class="left">' .
            "<a href=\"{$url}\">{$title}</a>" .
        '</div>';
    }
}
