<?php

class TM_NavigationPro_Model_Sibling extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('navigationpro/sibling');
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
}
