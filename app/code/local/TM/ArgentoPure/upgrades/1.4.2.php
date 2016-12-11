<?php

class TM_ArgentoPure_Upgrade_1_4_2 extends TM_Core_Model_Module_Upgrade
{
    public function up()
    {
        $tmp = array(
            'tab_color'        => 'tab-text_color',
            'tab-hover_color'  => 'tab-hover-text_color',
            'tab-active_color' => 'tab-active-text_color',
            'tab_font-weight'  => 'tab-text_font-weight',
            'tab_font-size'    => 'tab-text_font-size',
            'tab_font-family'  => 'tab-text_font-family',
            'tab_padding'      => 'tab-text_padding'
        );
        $mapping = array();
        $prefix  = 'argento_pure/tabs/';
        foreach ($tmp as $oldPath => $newPath) {
            $mapping[$prefix . $oldPath] = $prefix . $newPath;
        }
        $this->renameConfigPath($mapping);
    }
}
