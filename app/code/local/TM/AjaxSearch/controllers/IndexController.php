<?php
class TM_AjaxSearch_IndexController  extends Mage_Core_Controller_Front_Action
{
    const TO_UTF8 = 'tm_ajaxsearch/general/forced_encode_result_utf8';
    private function _sendJson(array $data = array())
    {
        function utf8_encode_all($data) // -- It returns $data encoded to UTF8
        {
          if (is_string($data)) {
              return utf8_encode($data);
          }
          if (!is_array($data)) {
              return $data;
          }
          $return = array();
          foreach($data as $i => $d) {
              $return[$i] = utf8_encode_all($d);
          }
          return $return;
        }

        $isForcedToUtf8 = (bool) Mage::getStoreConfig(self::TO_UTF8);
        if ($isForcedToUtf8) {
          $data = utf8_encode_all($data);
        }

        $json = Zend_Json::encode($data);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($json);
//        @header('Content-type: application/json');
//        echo json_encode($data);
//        exit();
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        if ($request && $request->isXmlHttpRequest()) {
            $block = $this->getLayout()->createBlock('ajaxsearch/result');
            $this->_sendJson($block->getSuggestions());
        } else {
            $this->_redirectReferer();
        }
    }
}