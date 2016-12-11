<?php

class TM_ProLabels_Block_Label extends TM_ProLabels_Block_Content_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tm/prolabels/content/labels.phtml');
        $this->mode = 'product';
    }

    public function getProduct()
    {
        return Mage::registry('current_product');
    }

}
