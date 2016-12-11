<?php

class TM_ProLabels_Model_Entity_Attribute_Backend_Boolean_Config extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function afterLoad($object)
    {
        if(!$object->hasData($this->getAttribute()->getAttributeCode()))
        {
            $object->setData($this->getAttribute()->getAttributeCode(), $this->getDefaultValue());
        }

    }

    public function getAllOptions()
	{
		return array();
	}
}
