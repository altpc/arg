<?php
class TM_AskIt_IndexController extends Mage_Core_Controller_Front_Action
{
    protected function _getPageSubtitle()
    {
        $block = $this->getLayout()->getBlock('askit');
        $pageVarName = false;
        if ($block) {
            $pageVarName = $block->getPageVarName();
        }
        if (empty($pageVarName)) {
            $pageVarName = 'askit_page';
        }
        $page = $this->getRequest()->getParam($pageVarName, 1);
        $title = '';
        if ($page > 1) {
            $title = $this->__(' - Page %d', $page);
        }
        return $title;
    }

    public function testupAction()
    {
        $resName = 'askit_setup';
        $test = new TM_AskIt_Model_Resource_Setup_Test($resName);
        // $test->setDbVersion('2.3.0');
        $prefix = 'xx_rm_';
        $test->setTablePrefix($prefix);
        // $test->dropTempTables();
        // $test->install22();
        // $test->fill22();
        // $test->upgrade222223();
        // $test->upgrade223224();
        // $test->upgrade224225();
        // $test->dropTempTables('xx_rm_');
        // $test->install23();
        // $test->dropTempTables('xx_rm_');

        // $test->generateAll();
        echo 'Done';
        die;
    }

    public function indexAction()
    {
        $this->loadLayout();

        $block = $this->getLayout()->getBlock('head');
        $title = $this->__('Store discussion') . $this->_getPageSubtitle();
        $block->setTitle($title)
            ->setKeywords("discussion")
            ->setDescription($title)
        ;
        $this->renderLayout();
    }

    public function customerAction()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('customer/account/login');
        }
        $this->loadLayout();

        $block = $this->getLayout()->getBlock('head');
        $title = $this->__('Store discussion') . $this->_getPageSubtitle();
        $block->setTitle($title)
            ->setKeywords("discussion")
            ->setDescription($title)
        ;
        $this->renderLayout();
    }

    public function productAction()
    {
        $product = Mage::registry('current_product');
        $this->loadLayout();

        if ($product) {
            $block = $this->getLayout()->getBlock('head');
            $title = $this->__('Product Questions – %s', $product->getName())
                . $this->_getPageSubtitle();
            $block->setTitle($title)
//                ->setKeywords("your, keywords, anything")
                ->setDescription($title)
            ;
        }
        $this->renderLayout();
    }

    public function categoryAction()
    {
        $category = Mage::registry('current_category');
        $this->loadLayout();

        if ($category) {
            $block = $this->getLayout()->getBlock('head');
            $title = $this->__('Category Questions – %s', $category->getName())
                . $this->_getPageSubtitle();
            $block->setTitle($title)
//                ->setKeywords("your, keywords, anything")
                ->setDescription($title)
            ;
        }
        $this->renderLayout();
    }

    public function pageAction()
    {
        $this->loadLayout();

        $pageId = $this->getRequest()->getParam('page_id');
        $page = Mage::getModel('cms/page')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($pageId);

        if ($page) {
            $block = $this->getLayout()->getBlock('head');
            $title = $this->__('Cms Page Questions – %s', $page->getTitle())
                . $this->_getPageSubtitle();
            $block->setTitle($title)
//                ->setKeywords("your, keywords, anything")
                ->setDescription($title)
            ;
        }
        $this->renderLayout();
    }

    public function saveQuestionAction()
    {
        $author = (string) $this->getRequest()->getParam('askitCustomer');
        $author = Mage::helper('core')->stripTags($author);
        $email = (string) $this->getRequest()->getParam('askitEmail');
        $email = Mage::helper('core')->stripTags($email);
        if (!$author || !$email) {
            Mage::getSingleton('core/session')->addError('Email and Name required');
            $this->_redirectReferer();
            return;
        }

        $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        if (!$isLoggedIn && !Mage::getStoreConfig('askit/general/allowedGuestQuestion')) {
            Mage::getSingleton('core/session')->addError('Your must login');
            $this->_redirectReferer();
            return;
        }

        $text = (string) $this->getRequest()->getParam('text');

        if (Mage::getStoreConfig('askit/general/enableAkismet') &&
            Mage::getModel('akismet/service') &&
            Mage::getModel('akismet/service')->isSpam($author, $email, $text)) {

            $this->_redirectReferer();
            return;
        }
        $text = Mage::helper('core')->stripTags($text);

        $itemId     = (int) $this->getRequest()->getParam('item_id');
        $itemTypeId = (int) $this->getRequest()->getParam('item_type_id');
        $isPrivate  = false;

        $question = Mage::getModel('askit/message');
        if ($isLoggedIn) {
            $question->setCustomerId(
                Mage::getSingleton('customer/session')->getCustomerId()
            );
            $isPrivate = (bool) $this->getRequest()->getParam('askitPrivate', 0);
        }
        $defaultQuestionStatus = Mage::getStoreConfig('askit/general/defaultQuestionStatus');//pending
        $question
            ->setText($text)
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setHint(0)
            ->setParentId(null)
            ->setCustomerName($author)
            ->setEmail($email)
            ->setCreatedTime(now())
            ->setUpdateTime(now())
            ->setStatus($defaultQuestionStatus)
            ->setPrivate($isPrivate)
            ->save()
        ;

        $item = Mage::getModel('askit/item');
        $item->setData(array(
            'message_id'   => $question->getId(),
            'item_id'      => $itemId,
            'item_type_id' => $itemTypeId
        ))->save();

        /* Now send email to admin about new question */
        Mage::getSingleton('core/session')->addSuccess(
            Mage::helper('askit')->__(
                'Your question has been accepted for moderation'
            )
        );
        Mage::dispatchEvent('askit_item_admin_notify_save_after', array(
            'data_object' => $question
        ));

        $this->_redirectReferer();
    }

    public function saveAnswerAction()
    {
        $isAllowedGuestAnswer = Mage::getStoreConfig('askit/general/allowedGuestAnswer');
        if (!$isAllowedGuestAnswer) {
            if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('askit')->__(
                        'Sorry, only logged in customer can add self answer.'
                    )
                );
                $this->_redirectReferer();
                return;
            }
        }
        $customerName = (string) $this->getRequest()->getParam('askitCustomer');
        $customerName = Mage::helper('core')->stripTags($customerName);
        $email = (string) $this->getRequest()->getParam('askitEmail');
        $email = Mage::helper('core')->stripTags($email);
        if (!$customerName
            || !$email
            || !Mage::getStoreConfig('askit/general/allowedCustomerAnswer')) {

            $this->_redirectReferer();
            return;
        }

        $text     = (string) $this->getRequest()->getParam('text');
        $text = Mage::helper('core')->stripTags($text);
        $questionId = (string) $this->getRequest()->getParam('parent_id');
        $question   = Mage::getModel('askit/message')->load($questionId);

        $customerId = (int) Mage::getSingleton('customer/session')->getCustomerId();
        if (0 === $customerId) {
            $customerId = null;
        }
        $answer = Mage::getModel('askit/message');

        $storeId = $question->getStoreId();
        $defaultAnswerStatus = Mage::getStoreConfig('askit/general/defaultAnswerStatus');//pending
        $answer
            ->setText($text)
            ->setStoreId($storeId)
            ->setCustomerId($customerId)
            ->setHint(0)
            ->setParentId($questionId)
            ->setCustomerName($customerName)
            ->setEmail($email)
            ->setCreatedTime(now())
            ->setUpdateTime(now())
            ->setStatus($defaultAnswerStatus)
            ->save();

        Mage::dispatchEvent('askit_item_admin_notify_save_after', array(
            'data_object' => $answer
        ));

        Mage::getSingleton('core/session')->addSuccess(
            Mage::helper('askit')->__('Your answer has been accepted')
        );
        $this->_redirectReferer();
    }

    public function saveHintAction()
    {
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            Mage::getSingleton('core/session')->addError(
                Mage::helper('askit')->__('Sorry, only logged in customer can hint.')
            );
            $this->_redirectReferer();
        }

        $messageId = (int) $this->getRequest()->getParam('message_id');
        $customerId = (int) Mage::getSingleton('customer/session')->getCustomerId();

        $vote = Mage::getModel('askit/vote');

        if ($vote->isVoted($messageId, $customerId)) {
            Mage::getSingleton('core/session')->addError(
                Mage::helper('askit')->__('Sorry, already voted')
            );
            $this->_redirectReferer();
        }
        $add = (int) $this->getRequest()->getParam('add');
        $add = $add > 0 ? 1 : -1;

        $item = Mage::getModel('askit/message')->load($messageId);
        $item->setHint($item->getHint() + $add);
        $item->save();

        $vote->setData(array(
            'message_id' => $messageId,
            'customer_id' => $customerId,
        ));
        $vote->save();
        $this->_redirectReferer();
    }

    public function rssAction()
    {
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function viewAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
