<?php

class TM_ProLabels_Model_Entity_Attribute_Backend_Text extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function afterLoad($object)
    {
        if(!$object->hasData($this->getAttribute()->getAttributeCode()))
        {
            $object->setData($this->getAttribute()->getAttributeCode(), $this->getDefaultValue());
        }
    }
}
