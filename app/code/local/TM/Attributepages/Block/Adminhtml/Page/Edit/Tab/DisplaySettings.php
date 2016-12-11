<?php

class TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_DisplaySettings
    extends TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('page_');

        $model = $this->getPage();

        $layoutFieldset = $form->addFieldset('layout_fieldset', array(
            'legend' => Mage::helper('cms')->__('Page Layout'),
            'class'  => 'fieldset-wide',
            'disabled' => $isElementDisabled
        ));

        $layouts = Mage::getSingleton('page/source_layout')->toOptionArray();
        array_unshift($layouts, array(
            'value' => '',
            'label' => Mage::helper('catalog')->__('No layout updates')
        ));
        $layoutFieldset->addField('root_template', 'select', array(
            'label'  => Mage::helper('cms')->__('Layout'),
            'title'  => Mage::helper('cms')->__('Layout'),
            'name'   => 'root_template',
            'values' => $layouts,
            'disabled' => $isElementDisabled
        ));
        if (!$model->getId()) {
            $model->setRootTemplate(Mage::getSingleton('page/source_layout')->getDefaultValue());
        }

        $layoutFieldset->addField('layout_update_xml', 'textarea', array(
            'name'      => 'layout_update_xml',
            'label'     => Mage::helper('cms')->__('Layout Update XML'),
            'style'     => 'height:12em;',
            'disabled'  => $isElementDisabled
        ));

        $layoutFieldset = $form->addFieldset('display_fieldset', array(
            'legend' => Mage::helper('catalog')->__('Display Settings'),
            // 'class'  => 'fieldset-wide',
            'disabled' => $isElementDisabled
        ));

        $layoutFieldset->addField('display_mode', 'select', array(
            'label'  => Mage::helper('catalog')->__('Display Mode'),
            'title'  => Mage::helper('catalog')->__('Display Mode'),
            'name'   => 'display_mode',
            'value'  => TM_Attributepages_Model_Entity::DISPLAY_MODE_MIXED,
            'values' => array(
                TM_Attributepages_Model_Entity::DISPLAY_MODE_MIXED
                    => Mage::helper('attributepages')->__('Description and children'),

                TM_Attributepages_Model_Entity::DISPLAY_MODE_DESCRIPTION
                    => Mage::helper('attributepages')->__('Description only'),

                TM_Attributepages_Model_Entity::DISPLAY_MODE_CHILDREN
                    => Mage::helper('attributepages')->__('Children only')
            ),
            'disabled' => $isElementDisabled
        ));

        if ($model->isAttributeBasedPage()) {
            $layoutFieldset->addField('column_count', 'text', array(
                'label' => Mage::helper('attributepages')->__('Columns Count'),
                'title' => Mage::helper('attributepages')->__('Columns Count'),
                'note'  => Mage::helper('attributepages')->__('1 — 8 columns are supported'),
                'name'  => 'column_count',
                'value' => 4,
                'disabled' => $isElementDisabled
            ));

            $layoutFieldset->addField('group_by_first_letter', 'select', array(
                'label' => Mage::helper('attributepages')->__('Group Options by First Letter'),
                'title' => Mage::helper('attributepages')->__('Group Options by First Letter'),
                'name'  => 'group_by_first_letter',
                'value' => 0,
                'values' => array(
                    '1' => Mage::helper('catalog')->__('Yes'),
                    '0' => Mage::helper('catalog')->__('No')
                ),
                'disabled' => $isElementDisabled
            ));

            $layoutFieldset->addField('listing_mode', 'select', array(
                'label'  => Mage::helper('attributepages')->__('Listing Mode'),
                'title'  => Mage::helper('attributepages')->__('Listing Mode'),
                'name'   => 'listing_mode',
                'value'  => TM_Attributepages_Model_Entity::LISTING_MODE_LINK,
                'values' => array(
                    TM_Attributepages_Model_Entity::LISTING_MODE_IMAGE
                        => Mage::helper('widget')->__('Images'),
                    TM_Attributepages_Model_Entity::LISTING_MODE_LINK
                        => Mage::helper('adminhtml')->__('Links')
                ),
                'disabled' => $isElementDisabled
            ));

            $layoutFieldset->addField('image_width', 'text', array(
                'label' => Mage::helper('attributepages')->__('Image Width'),
                'title' => Mage::helper('attributepages')->__('Image Width'),
                'name'  => 'image_width',
                'value' => 200,
                'disabled' => $isElementDisabled
            ));
            $layoutFieldset->addField('image_height', 'text', array(
                'label' => Mage::helper('attributepages')->__('Image Height'),
                'title' => Mage::helper('attributepages')->__('Image Height'),
                'name'  => 'image_height',
                'value' => 150,
                'disabled' => $isElementDisabled
            ));
        }

        /**
         * Pagination:
         *  all pages
         *  standard pagination
         *
         *  not pagination! - letter based pagination A|B|C|D... (letters per page: A-B|C-D|...)
         *      Letter pagination could have many options per page
         *      So letter - is not pagination, but filter
         */

         /**
          * show letter filter: A-C|D-F|...
          *     Automatic: x letters
          */

         /**
          * Search by option name
          */

        $form->addValues($model->getData());
        $form->setFieldNameSuffix('attributepage');
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('catalog')->__('Display Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('catalog')->__('Display Settings');
    }
}
