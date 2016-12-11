<?php
class TM_ProLabels_Model_System_Config_Backend_Percent extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $value = $this->getValue();
		if ($value === null)
		{
			$value = 0;
		}
		if ($value && !is_numeric($value))
		{
			$value = 0;
		}
		if ($value && is_numeric($value) && ($value < 0) )
		{
			$value = 0;
		}
		if ($value && is_numeric($value) && ($value > 100) )
		{
			$value = 100;
		}
		$this->setValue($value);
        return $this;
    }
}