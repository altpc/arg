<?php
    $installer = $this;
    $installer->startSetup();

    $inst = new Mage_Eav_Model_Entity_Setup('core_setup');
    
    /* $inst->addAttribute('catalog_product', 'prolabel_p_display', array(
            'backend'       => 'prolabels/entity_attribute_backend_boolean_config',
            'source'        => 'prolabels/entity_attribute_source_boolean_config',
            'label'         => 'Prolabels Product Display',
            'group'         => 'ProLabel Product',
            'input'         => 'select',
            'class'         => 'validate-digit',
            'global'        => false,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'default'       => '0',
            'visible_on_front' => false
        ));
    
    $inst->addAttribute('catalog_product', 'prolabel_p_position', array(
            'backend'       => 'prolabels/entity_attribute_backend_position',
            'source'        => 'prolabels/entity_attribute_source_position',
            'label'         => 'Prolabels Product Position',
            'group'         => 'ProLabel Product',
            'input'         => 'select',
            'class'         => 'validate-digit',
            'global'        => false,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'default'       => 'top-left',
            'visible_on_front' => false
        ));

    $inst->addAttribute('catalog_product', 'prolabel_p_image', array(
            'backend'       => 'prolabels/entity_attribute_backend_image',
            'label'         => 'Prolabels Product Image',
            'group'			=> 'ProLabel Product',
            'input'         => 'image',
            'class'         => 'validate-digit',
            'global'        => false,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'default'       => '',
            'visible_on_front' => false
        ));

    $inst->addAttribute('catalog_product', 'prolabel_p_text', array(
            'source'        => 'prolabels/entity_attribute_source_text',
            'group'         => 'ProLabel Product',
            'label'         => 'Prolabels Product Image text',
            'input'         => 'text',
            'class'         => '',
            'global'        => false,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'visible_on_front' => false
        ));

    $inst->addAttribute('catalog_product', 'prolabel_p_positionstyle', array(
            'group'         => 'ProLabel Product',
            'label'         => 'Prolabels Product Position Style',
            'input'         => 'text',
            'global'        => false,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'visible_on_front' => false
        ));

    $inst->addAttribute('catalog_product', 'prolabel_p_fontstyle', array(
            'group'         => 'ProLabel Product',
            'label'         => 'Prolabels Product Font Style',
            'input'         => 'text',
            'global'        => false,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'visible_on_front' => false
        ));

    $inst->addAttribute('catalog_product', 'prolabel_c_display', array(
            'backend'       => 'prolabels/entity_attribute_backend_boolean_config',
            'source'        => 'prolabels/entity_attribute_source_boolean_config',
            'label'         => 'Prolabels Category Display',
            'group'         => 'ProLabel Category',
            'input'         => 'select',
            'class'         => 'validate-digit',
            'global'        => true,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'default'       => '0',
            'visible_on_front' => false
        ));

    $inst->addAttribute('catalog_product', 'prolabel_c_position', array(
            'backend'       => 'prolabels/entity_attribute_backend_position',
            'source'        => 'prolabels/entity_attribute_source_position',
            'label'         => 'Prolabels Category Position',
            'group'         => 'ProLabel Category',
            'input'         => 'select',
            'class'         => 'validate-digit',
            'global'        => true,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'default'       => 'top-left',
            'visible_on_front' => false
        ));

    $inst->addAttribute('catalog_product', 'prolabel_c_image', array(
            'backend'       => 'prolabels/entity_attribute_backend_image',
            'label'         => 'Prolabels Category Image',
            'group'         => 'ProLabel Category',
            'input'         => 'image',
            'class'         => 'validate-digit',
            'global'        => true,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'default'       => '',
            'visible_on_front' => false
        ));

    $inst->addAttribute('catalog_product', 'prolabel_c_text', array(
            'source'        => 'prolabels/entity_attribute_source_text',
            'group'         => 'ProLabel Category',
            'label'         => 'Prolabels Category Image text',
            'input'         => 'text',
            'global'        => true,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'visible_on_front' => false
        ));

    $inst->addAttribute('catalog_product', 'prolabel_c_positionstyle', array(
            'source'        => 'prolabels/entity_attribute_source_text',
            'group'         => 'ProLabel Category',
            'label'         => 'Prolabels Category Position style',
            'input'         => 'text',
            'global'        => true,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'visible_on_front' => false
        ));

    $inst->addAttribute('catalog_product', 'prolabel_c_fontstyle', array(
            'source'        => 'prolabels/entity_attribute_source_text',
            'group'         => 'ProLabel Category',
            'label'         => 'Prolabels Category Font Style',
            'input'         => 'text',
            'global'        => true,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => false,
            'visible_on_front' => false
        )); */

    $installer->endSetup();
?>