<?php

class TM_ProLabels_Model_Entity_Attribute_Backend_Image extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function beforeSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName());

        if (is_array($value) && !empty($value['delete'])) {
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()
                ->saveAttribute($object, $this->getAttribute()->getName());
            return;
        }

        $path = Mage::getBaseDir('media') . '/prolabel/default/';

        try
        {
            $uploader = new Varien_File_Uploader($this->getAttribute()->getName());
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->save( $path );

            $object->setData($this->getAttribute()->getName(), $uploader->getUploadedFileName());
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
        } 
        catch (Exception $e)
        {
            return;
        }
    }
    
    public function afterLoad($object)
    {
        if ($value = $object->getData($this->getAttribute()->getName())) {
            $object->setData($this->getAttribute()->getName(), '../../prolabel/default/' . $value);
        }
    }
}
