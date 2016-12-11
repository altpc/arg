<?php
class TM_AskIt_Block_List_Recent extends TM_AskIt_Block_List_Abstract
implements Mage_Widget_Block_Interface
{
    protected $_template = 'tm/askit/question/list/recent.phtml';

    public function getQuestionLimit()
    {
        return $this->getData('count');
    }
}
