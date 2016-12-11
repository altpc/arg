<?php
class TM_LightboxPro_Model_System_Config_Source_Position
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $positions = "above,top left,top,top center,top right,leftpanel,left,middle left,middle center,middle right,right,rightpanel,bottom left,bottom center,bottom right,below";
        $result = array();
        foreach (explode(',', $positions) as $_position) {
            $result[] = array(
                'value' => $_position, 
                'label' => Mage::helper('lightboxpro')->__($_position)
            ); 
        } 
        return $result;
    }
}
