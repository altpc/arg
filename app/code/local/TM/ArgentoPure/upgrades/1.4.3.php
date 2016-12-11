<?php

class TM_ArgentoPure_Upgrade_1_4_3 extends TM_Core_Model_Module_Upgrade
{
    public function up()
    {
        $tmp = array(
            'block-title_font-family'   => 'block-title-text_font-family',
            'block-title_font-size'     => 'block-title-text_font-size',
            'block-title_font-weight'   => 'block-title-text_font-weight',
            'block-title_color'         => 'block-title-text_color'
        );
        $mapping = array();
        $prefix  = 'argento_pure/font/';
        foreach ($tmp as $oldPath => $newPath) {
            $mapping[$prefix . $oldPath] = $prefix . $newPath;
        }
        $this->renameConfigPath($mapping);
    }
}
