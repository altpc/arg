<?php
class TM_ReviewReminder_Block_Products extends Mage_Core_Block_Template
{
    public function getReviewLink($id)
    {
        return Mage::getUrl('review/product/list', array('id'=> $id));
    }
}