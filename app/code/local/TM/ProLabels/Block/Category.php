<?php

class TM_ProLabels_Block_Category extends TM_ProLabels_Block_Content_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tm/prolabels/content/labels.phtml');
        $this->mode = 'category';
    }

}
