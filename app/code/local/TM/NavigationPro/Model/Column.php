<?php

class TM_NavigationPro_Model_Column extends Mage_Core_Model_Abstract
{
    const TYPE_SUBCATEGORY = 'subcategory';
    const TYPE_HTML        = 'html';
    const TYPE_LAYERED     = 'layered';

    const DIRECTION_HORIZONTAL = 'horizontal';
    const DIRECTION_VERTICAL   = 'vertical';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('navigationpro/column');
    }

    /**
     * Overriden to convert the json saved configuration to array style
     *
     * @param string $key
     * @param mixed $value
     * @return TM_NavigationPro_Model_Column
     */
    public function setData($key, $value = null)
    {
        parent::setData($key, $value);

        if ((is_array($key) && array_key_exists('configuration', $key))
            || 'configuration' === $key) {

            if (is_array($key)) {
                $value = $key['configuration'];
            }

            try {
                $config = Mage::helper('core')->jsonDecode($value);
                if (!is_array($config)) {
                    $config = array();
                }
            } catch (Exception $e) {
                $config = array();
            }

            foreach ($config as $key => $value) {
                parent::setData($key, $value);
            }
        }
        return $this;
    }

    /**
     * The only way to set the configuration in json format before save
     *
     * @param string $value
     * @return TM_NavigationPro_Model_Column
     */
    public function setConfiguration($value)
    {
        $this->_data['configuration'] = $value;
        return $this;
    }

    public function getDefaultData()
    {
        return array(
            'is_active'           => 1,
            'sort_order'          => 50,
            'width'               => 160,
            'type'                => 'subcategory',
            'style'               => 'dropdown',
            'direction'           => 'horizontal',
            'columns_count'       => 2,
            'levels_per_dropdown' => 1
        );
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        $width = $this->getData('width');
        if (is_numeric($width)) {
            $width .= 'px';
        }
        return $width;
    }

    /**
     * @return int
     */
    public function getColumnsCount()
    {
        $count = $this->getData('columns_count');
        if (!$count || !is_numeric($count)) {
            $count = 1;
        }
        return $count;
    }

    public function isHorizontal()
    {
        return $this->getDirection() === self::DIRECTION_HORIZONTAL;
    }

    public function isVertical()
    {
        return $this->getDirection() === self::DIRECTION_VERTICAL;
    }
}
