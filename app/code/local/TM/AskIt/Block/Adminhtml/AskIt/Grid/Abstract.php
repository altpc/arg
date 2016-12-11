<?php

class TM_AskIt_Block_Adminhtml_AskIt_Grid_Abstract extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _getCssClassByStatus($value)
    {
        switch ($value) {
            case TM_AskIt_Model_Status::STATUS_DISAPROVED:
                $class = 'critical';
                break;
            case TM_AskIt_Model_Status::STATUS_APROVED:
                $class = 'minor';
                break;
            case TM_AskIt_Model_Status::STATUS_CLOSE:
                $class = 'notice';
                break;
            case TM_AskIt_Model_Status::STATUS_PENDING:
            default:
                $class = 'major';
                break;
        }

        return $class;
    }

    protected function _getCssClassByStatus2($value)
    {
        switch ($value) {
            case TM_AskIt_Model_Status::STATUS_DISAPROVED:
                $class = 'critical';
                break;
            case TM_AskIt_Model_Status::STATUS_APROVED:
                $class = 'notice';
                break;
//            case TM_AskIt_Model_Status::STATUS_CLOSE:
//                $class = 'notice';
//            break;
            case TM_AskIt_Model_Status::STATUS_PENDING:
            default:
                $class = 'minor';
                break;
        }

        return $class;
    }

    protected function _getCell($value, $class = 'notice')
    {
        return "<span class=\"grid-severity-{$class}\"><span>{$value}</span></span>";
    }

    public function decorateItemName($value, $row, $column, $isExport)
    {
        $item = Mage::helper('askit')->getItem($row);
        return  "<a  href=\"{$item->getBackendItemUrl()}\" >{$item->getName()}</a>";
    }

    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = $this->_getCssClassByStatus($row->status);
        return $this->_getCell($value, $class);
    }

    public function decoratePrivate($value, $row, $column, $isExport)
    {
        $class = $row->private ? 'major' : 'notice';
        return $this->_getCell($value, $class);
    }

    public function decorateCountnumber($value, $row, $column, $isExport)
    {
        $collection = Mage::getModel('askit/message')->getCollection();
        $collection->addParentIdFilter($row->getId());

        $return = "<center>{$value}<br/>";

        if ($collection->count() > 0) {
            $statusses = Mage::getSingleton('askit/status')->getAnswerOptionArray();
            $item = $collection->getLastItem();

            $text = Mage::helper('core')->escapeHtml(
                substr($item->getText(), 0, 12)
            ) . '...';
            $url = $this->getUrl('*/*/edit', array('id' => $item->getId()));
            $return .=
                Mage::helper('askit')->__('Last answer')
                . ": <br/><a href=\"{$url}\" >{$text}</a><br/>"
        ;

//        foreach ($collection as $item) {
            $_author = $item->getCustomerName();
            $_value = $item->status;
            $_class = $this->_getCssClassByStatus2($_value);
            $return .= Mage::helper('askit')->__('Author') . ' "' . $_author . '" '
                . $this->_getCell($statusses[$_value], $_class)
            ;
//        }
        }
        $return .= "</center>";
        return $return;
    }
}
