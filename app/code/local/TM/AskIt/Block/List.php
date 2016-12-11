<?php
class TM_AskIt_Block_List extends TM_AskIt_Block_List_Abstract
{
    protected  $_actionsParams = array();

    /**
     *
     * @var string
     */
    protected $_pageVarName = 'askit_page';

    /**
     *
     * @return string
     */
    public function getPageVarName()
    {
        return $this->_pageVarName;
    }

    protected function _getPage()
    {
        $pageVarName = $this->getPageVarName();
        $page = $this->getRequest()->getParam($pageVarName, 1);
        return $page;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    protected function _beforeToHtml()
    {
        $questionViewBlock = $this->getLayout()
            ->createBlock('core/template')
            ->setTemplate('tm/askit/question/view.phtml')
        ;

        $newAnswerFormBlock = $this->getChild('askit_new_answer_form');
        $questionViewBlock->setChild('askit_new_answer_form', $newAnswerFormBlock);

        $answerListBlock = $this->getLayout()
            ->createBlock('core/template')
            ->setTemplate('tm/askit/answer/list.phtml')
        ;

        $answerViewBlock = $this->getLayout()
            ->createBlock('core/template')
            ->setTemplate('tm/askit/answer/view.phtml')
        ;
        $answerListBlock->setChild('askit_answer_view', $answerViewBlock);

        $questionViewBlock->setChild('askit_answer_list', $answerListBlock);

        $this->setChild('askit_question_view', $questionViewBlock);

        parent::_prepareLayout();
        $blockName = $this->getNameInLayout() . 'pager';

        $pager = $this->getLayout()->createBlock('askit/html_pager', $blockName);

        $limit = $this->getQuestionLimit();
        $pager->setLimit($limit);
        $pager->setPageVarName($this->getPageVarName());

        $pager->setAvailableLimit(array(1 => $limit));

        $collection = $this->_getCollection(false);
        $collection->setPageSize($limit);
        $collection->setCurPage($this->_getPage());

        $request = $this->getRequest();
        $fullActionName = $request->getModuleName() . '_'
            . $request->getControllerName() . '_'
            . $request->getActionName();

        $pager->setCollection($collection);

        // \Zend_Debug::dump((string) $collection->getSelect());

        $pager->setShowAmounts(false);

        $id = $this->getRequest()->getParam('id', false);

        if ($id && $fullActionName == 'askit_index_view') {
            $collection->getSelect()->where('main_table.id = ?', $id);
        } else {

            $this->setChild('pager', $pager);
        }
//        $this->_getCollection()->load();

        $this->_actionsParams['_secure'] = $this->getRequest()->isSecure();
        $this->_actionsParams[Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED] =
                Mage::helper('core/url')->getEncodedUrl();
        return parent::_beforeToHtml();
    }

    public function getQuestionLimit()
    {
        return Mage::getStoreConfig('askit/general/questionLimitList');
    }

    public function getNewQuestionFormAction()
    {
        if (!isset($this->_actionsParams['item_id'])
            || !isset($this->_actionsParams['item_type_id'])) {

            return;
        }
        return Mage::getUrl('askit/index/saveQuestion', $this->_actionsParams);
    }
}
