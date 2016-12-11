<?php

class TM_AskIt_Helper_Data extends Mage_Core_Helper_Abstract
{
    //$message = Mage::helper('askit')->getItem($data);
    public function getItem($message)
    {
        $type = isset($message['item_type_id']) ? $message['item_type_id'] : null;
        $id = isset($message['item_id']) ? $message['item_id'] : null;
        $_item = false;
        $_prefix = null;
        $return = new Varien_Object();

        switch ($type) {
            case TM_AskIt_Model_Item_Type::PRODUCT_ID:
                $_item    = Mage::getModel('catalog/product')->load($id);

                $_urlPath = $_item->getUrlPath();
                $_frontendItemUrl = $_item->getProductUrl();
                $_name    = $_item->getName();
                $_typeName = 'Product';

                $_backendItemUrl = Mage::helper("adminhtml")->getUrl(
                    'adminhtml/catalog_product/edit',
                    array('id'=> $_item->getId())
                );
                $_prefix = 'product';
                break;
            case TM_AskIt_Model_Item_Type::PRODUCT_CATEGORY_ID:
                $_item    = Mage::getModel('catalog/category')->load($id);

                $_urlPath = $_item->getUrlPath();
                $_frontendItemUrl = $_item->getUrl();
                $_name    = $_item->getName();
                $_typeName = 'Catalog Category';

                $_backendItemUrl = Mage::helper("adminhtml")->getUrl(
                    'adminhtml/catalog_category/edit',
                    array('id'=> $_item->getId())
                );
                $_prefix = 'category';

                break;
            case TM_AskIt_Model_Item_Type::CMS_PAGE_ID:
                $_item = Mage::getModel('cms/page')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($id);

                $_urlPath = $_item->getIdentifier();
                $_frontendItemUrl = Mage::helper('cms/page')->getPageUrl(
                    $_item->getId()
                );
                $_name    = $_item->getTitle();
                $_typeName = 'Cms Page';

                $_backendItemUrl = Mage::helper("adminhtml")->getUrl(
                    'adminhtml/cms_page/edit',
                    array('page_id'=> $_item->getId())
                );
                $_prefix = 'page';
                break;

            default:
                $_urlPath = '';
                $_frontendItemUrl = '';
                $_name = 'unknown';
                $_typeName = 'unknow';
                $_backendItemUrl = '#';
                break;
        }
        if ($_item) {
            $return->setRawItem($_item);
        }
        $return->setUrlPath($_urlPath)
            ->setFrontendItemUrl($_frontendItemUrl)
            ->setName($_name)
            ->setTypeName($_typeName)
            ->setBackendItemUrl($_backendItemUrl)
            ->setPrefix($_prefix)
        ;
        return $return;
    }

    public function getAskitActionHref($message, $params = array())
    {
        $messageTypeId = isset($message['item_type_id']) ? $message['item_type_id'] : null;
        $messageId = isset($message['item_id']) ? $message['item_id'] : null;

        switch ($messageTypeId) {
            case TM_AskIt_Model_Item_Type::PRODUCT_ID:
                $_item = Mage::getModel('catalog/product')
                    ->load($messageId);

                $_url = $_item->getUrlPath();
                if (empty($_url)) {
                    $_url = $_item->getUrlKey();
                }
                break;
            case TM_AskIt_Model_Item_Type::PRODUCT_CATEGORY_ID:
                $_item = Mage::getModel('catalog/category')
                    ->load($messageId);

                $_url = $_item->getUrlPath();

                break;
            case TM_AskIt_Model_Item_Type::CMS_PAGE_ID:
                $page = Mage::getModel('cms/page')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($messageId);

                $_url = $page->getIdentifier();
                break;

            default:
                return;
                break;
        }

        $url = Mage::getUrl(Mage::helper('askit')->getRouteUrlPrefix() . '/' .  $_url, $params);
//        $title = Mage::helper('askit')->__('View all related questions.');
        return $url;
    }

    public function getLinkHtml($item)
    {
        $_url = '';

        $collection = Mage::getModel('askit/message')->getCollection()
            ->addStatusFilter(array(
                TM_AskIt_Model_Status::STATUS_APROVED,
                TM_AskIt_Model_Status::STATUS_CLOSE
            ))
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addQuestionFilter()
            ->addPrivateFilter()
        ;
        if ($item) {
            $itemId = $item->getId();

            $class = get_class($item);
            switch ($class) {
                case 'Mage_Catalog_Model_Category':
                    $itemTypeId = TM_AskIt_Model_Item_Type::PRODUCT_CATEGORY_ID;
                    $_url = $item->getUrlPath();
                    break;
                case 'Mage_Cms_Model_Page':
                    $itemTypeId = TM_AskIt_Model_Item_Type::CMS_PAGE_ID;
                    $_url = $item->getIdentifier();
                    break;
                case 'Mage_Catalog_Model_Product':
                default:
                    $itemTypeId = TM_AskIt_Model_Item_Type::PRODUCT_ID;
                    $_url = $item->getUrlPath();
                    if (empty($_url)) {
                        $_url = $item->getUrlKey();
                    }
                    break;
            }
            $collection// = Mage::getModel('askit/message')->getCollection()
                ->addItemIdFilter($itemId)
                ->addItemTypeIdFilter($itemTypeId)
            ;
        }

        $href = Mage::getUrl($this->getRouteUrlPrefix()) . $_url;

        $count = count($collection->load());

        $title = Mage::helper('askit')->__(
            "Be the first to ask a question about this product"
        ) ;
        if ($count) {
            $title = Mage::helper('askit')->__("Ask a question (%d)", $count);
        }
        return "<a href=\"{$href}\">{$title}</a><br/>";
    }

    public function trim($text, $len, $delim = '...')
    {
        if (@mb_strlen($text) > $len) {
            $whitespaceposition = mb_strpos($text, " ", $len) - 1;
            if ($whitespaceposition > 0) {
                $chars = count_chars(mb_substr($text, 0, ($whitespaceposition + 1)), 1);
                $text = mb_substr($text, 0, ($whitespaceposition + 1));
            }
            return $text . $delim;
        }
        return $text;
    }

    public function getRouteUrlPrefix()
    {
        return Mage::getStoreConfig('askit/general/urlPrefix');
    }

    public function getHintAction($messageId, $add = true)
    {
        $params = array(
            'message_id' => $messageId,
            'add' => $add ? 1 : -1,
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED =>
                Mage::helper('core/url')->getEncodedUrl()
        );
        return Mage::getUrl('askit/index/saveHint', $params);
    }

    public function getAnswersCountTitle($count)
    {
        return $count < 1 ? $this->__('Not Answerred') :
            $count . ' ' . $this->__($count > 1 ? 'Answers' : 'Answer');
    }

    public function getNewAnswerFormAction($parentId)
    {
        if (empty($parentId)) {
            return;
        }
        $params = array();
        $params['parent_id'] = $parentId;
        return Mage::getUrl('askit/index/saveAnswer', $params);
    }

    /**
     *
     * @param TM_AskIt_Model_Message $message
     * @return string
     */
    public function getItemViewLink(TM_AskIt_Model_Message $message)
    {
        $message = $this->getItem($message);
        $prefix = $this->__($message->getPrefix());
        return "{$prefix} <a href=\"{$message->getFrontendItemUrl()}\">{$message->getName()}</a>";
    }

    public function getQuestionViewUrl($id = null)
    {
        if (null === $id) {
            return Mage::getUrl(Mage::helper('askit')->getRouteUrlPrefix());
        }
        $params = array('id' => $id);
        return Mage::getUrl('askit/index/view', $params);
    }
}
