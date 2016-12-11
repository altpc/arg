<?php

class TM_Easyslide_Model_Easyslide extends Mage_Core_Model_Abstract
{
    protected $_slides = array();

    public function _construct()
    {
        parent::_construct();
        $this->_init('easyslide/easyslide');
    }

    protected function _afterLoad()
    {
        $nivoData = unserialize($this->getNivoOptions());
        $nivoData['nivoeffect'] = explode(',', $nivoData['effect']);

        unset($nivoData['effect']);
        $this->addData($nivoData);

        parent::_afterLoad();
    }

    public function load($id, $field = null)
    {
        if (!is_numeric($id) && is_string($id)) {
            $field = 'identifier';
        }
        parent::load($id, $field);

        return $this;
    }

    public function loadSlides($activeOnly = false)
    {
        $slidesModel = Mage::getResourceSingleton('easyslide/easyslide_slides');
        $read = $slidesModel->getReadConnection();
        if ($read) {
            $select = $read->select()->from(array('slides'=>$slidesModel->getMainTable()))
                ->where('slides.slider_id = ?', $this->getId())
                ->order('slides.sort_order ASC');

            if ($activeOnly) {
                $select->where('slides.is_enabled = 1');
            }

            $this->_slides = $read->fetchAll($select);
        }
    }

    public function setSlides($slides)
    {
        $this->_slides = $slides;
        return $slides;
    }

    public function getSlides()
    {
        return $this->_slides;
    }
}
