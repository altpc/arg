<?php

$menus = array(
    'top' => array(
        'name'    => 'top',
        'columns' => array(
            array(
                'width' => 160
            )
        )
    ),
    'left' => array(
        'name'                => 'left',
        'levels_per_dropdown' => 2,
        'columns'             => array(
            array(
                'width' => 193
            )
        )
    ),
    'right' => array(
        'name'    => 'right',
        'style'   => 'accordion',
        'columns' => array(
            array(
                'width' => 193,
                'style' => 'accordion'
            )
        )
    )
);
$menuDefaults = array(
    'is_active'             => 1,
    'columns_mode'          => 'menu',
    'display_in_navigation' => 0,
    'levels_per_dropdown'   => 1,
    'style'                 => 'dropdown'
);
$columnDefaults = array(
    'is_active'           => 1,
    'sort_order'          => '50',
    'type'                => TM_NavigationPro_Model_Column::TYPE_SUBCATEGORY,
    'style'               => 'dropdown',
    'levels_per_dropdown' => 1,
    'direction'           => 'horizontal',
    'columns_count'       => 1,
    'width'               => 160
);

foreach ($menus as $menuData) {
    foreach ($menuData['columns'] as $i => $columnData) {
        $menuData['columns'][$i] = array_merge($columnDefaults, $columnData);
    }

    $menu = Mage::getModel('navigationpro/menu')
        ->setData(array_merge($menuDefaults, $menuData))
        ->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
        ->setSiblings(array())
        ->setContent(array())
        ->save();
}
