<?php
class TM_AskIt_Block_List_Customer extends TM_AskIt_Block_List
{
    protected $_template = 'tm/askit/question/list.phtml';

    protected function _prepareCollection()
    {
        $this->_collection->addCustomerFilter();
    }
}
