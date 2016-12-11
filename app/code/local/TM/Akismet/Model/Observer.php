<?php
class TM_Akismet_Model_Observer
{
    /**
     *
     * @param Varien_Event_Observer $observer
     */
    public function postContactUs(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('akismet/general/enabledOnContactUs')) {
            return;
        }

        /** @var $controllerAction Mage_Core_Controller_Varien_Action */
        $controllerAction = $observer->getEvent()->getControllerAction();
        if (!$controllerAction) {
            return;
        }
        $service = Mage::getModel('akismet/service');
        $post    = $controllerAction->getRequest()->getPost();

        $isSpam  = $service->isSpam(
            trim($post['name']),
            trim($post['email']),
            trim($post['comment'])
        );
        if ($isSpam) {
            Mage::getSingleton('customer/session')->addError('Spam!!!');

            $controllerAction->setFlag('', 'no-dispatch', true);

            $controllerAction->getResponse()->setRedirect(
                Mage::getUrl('*/*/')
            );
        }
    }

    /**
     *
     * @param Varien_Event_Observer $observer
     */
    public function postProductReview(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('akismet/general/enabledOnProductReview')) {
            return;
        }

        /** @var $controllerAction Mage_Core_Controller_Varien_Action */
        $controllerAction = $observer->getEvent()->getControllerAction();
        if (!$controllerAction) {
            return;
        }
        $service = Mage::getModel('akismet/service');

        if (!$data = Mage::getSingleton('review/session')->getFormData(true)) {
            $data = $controllerAction->getRequest()->getPost();
        }
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        $isSpam  = $service->isSpam(
            trim($data['nickname']), //$customer->getName(),
            $customer->getEmail(),
            trim($data['detail'])
        );
        if ($isSpam) {
            Mage::getSingleton('customer/session')->addError('Spam!!!');

            $controllerAction->setFlag('', 'no-dispatch', true);

            $controllerAction->getResponse()->setRedirect(
                Mage::getUrl('*/*/')
            );
        }
    }

//// example usage
//    /**
//     * dispatch 'helpmate_notify_admin_ticket_change'
//     *
//     * @param Varien_Event_Observer $observer
//     * @return TM_Akismet_Model_Observer
//     */
//    public function onchangeHelpmateTicket(Varien_Event_Observer $observer)
//    {
//        $service = Mage::getModel('akismet/service');
//
//        $theard  = $observer->getEvent()->getTheard();
//        $ticket  = $theard->getTicket();
//
//        $author = $ticket->getEmail();
//        if (null !== $ticket->getCustomerId()) {
//            $author = Mage::getModel('customer/customer')
//                ->load($ticket->getCustomerId())
//                ->getName();
//        }
//
//        $isSpam  = $service->isSpam(
//            $author,
//            $ticket->getEmail(),
//            $theard->getText() //. $ticket->getTitle()
//        );
//        if ($isSpam) {
//            new Mage_Exception('this is spam');
//        }
//
//        return $this;
//    }
}