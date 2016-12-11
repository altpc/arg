<?php
$installer = $this;

$installer->startSetup();

$installer->run("

  ALTER TABLE {$this->getTable('prolabels/label')}
    MODIFY `product_position_style` VARCHAR(255),
    MODIFY `product_font_style` VARCHAR(255),
    MODIFY `category_position_style` VARCHAR(255),
    MODIFY `category_font_style` VARCHAR(255);

  ALTER TABLE {$this->getTable('prolabels/system')}
    MODIFY `product_position_style` VARCHAR(255),
    MODIFY `product_font_style` VARCHAR(255),
    MODIFY `category_position_style` VARCHAR(255),
    MODIFY `category_font_style` VARCHAR(255);

");

$installer->endSetup();
