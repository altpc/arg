<?php

class TM_NavigationPro_Block_Adminhtml_Menu_Tab_Columns_Column extends Mage_Adminhtml_Block_Widget
{
    protected $_name = 'columns';
    protected $_id = 'column';
    protected $_values;
    protected $_itemCount = 1;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tm/navigationpro/menu/edit/columns/column.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('adminhtml')->__('Delete'),
                    'class' => 'delete delete-column'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getAddButtonId()
    {
        return $this->getLayout()
            ->getBlock('menu.columns')
            ->getChild('add_button')
            ->getId();
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getIsActiveSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{column_id}}_is_active',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{column_id}}][is_active]')
            ->setOptions(array(
                '1' => Mage::helper('cms')->__('Enabled'),
                '0' => Mage::helper('cms')->__('Disabled')
            ));

        return $select->getHtml();
    }

    public function getStatusExceptionsTemplate()
    {
        return $this->getLayout()
            ->createBlock(
                'navigationpro/adminhtml_menu_tab_status_exceptions',
                '',
                array(
                    'id_key'     => 'column_id',
                    'field_id'   => $this->getFieldId(),
                    'field_name' => $this->getFieldName()
                )
            )
            ->toHtml();
    }

    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{column_id}}_type',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{column_id}}][type]')
            ->setOptions(
                Mage::getModel('navigationpro/adminhtml_system_config_source_type')
                    ->toOptionArray()
            );

        return $select->getHtml();
    }

    public function getStyleSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{column_id}}_style',
                'class' => 'select column-style'
            ))
            ->setName($this->getFieldName().'[{{column_id}}][style]')
            ->setOptions(
                Mage::getModel('navigationpro/adminhtml_system_config_source_style')
                    ->toOptionArray()
            );

        return $select->getHtml();
    }

    public function getDirectionSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{column_id}}_direction',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{column_id}}][direction]')
            ->setOptions(
                Mage::getModel('navigationpro/adminhtml_system_config_source_direction')
                    ->toOptionArray()
            );

        return $select->getHtml();
    }

    /**
     * Escape data that may broke json format
     *
     * @return TM_NavigationPro_Model_Resource_Column_Collection
     */
    public function getColumnsData()
    {
        $collection = $this->getMenu()->getDropdownColumnsCollection()
            ->setOrder('sort_order', 'DESC') // need to sort DESC to match js insert on top logic
            ->setOrder('column_id', 'DESC');

        $collection->addContentToResult($this->getMenu()->getStoreId());
        foreach ($collection as $column) {
            $this->setItemCount($column->getId());
            $column->setItemCount($this->getItemCount());

            $textFields = array(
                'title',          'content',              'width',
                'sort_order',     'css_id',               'css_class',
                'css_styles',     'levels_per_dropdown',  'columns_count',
                'levels_to_load', 'max_items_count',
            );
            foreach ($textFields as $field) {
                $column->setData(
                    $field, $this->escapeHtml($column->getData($field))
                );
            }
        }
        return $collection;
    }

    public function getDefaultColumnData()
    {
        return Mage::getModel('navigationpro/column')->getDefaultData();
    }

    public function canDisplayUseDefault()
    {
        return (bool) $this->getRequest()->getParam('store');
    }

    public function getItemCount()
    {
        return $this->_itemCount;
    }

    public function setItemCount($itemCount)
    {
        $this->_itemCount = max($this->_itemCount, $itemCount);
        return $this;
    }

    public function getCategory()
    {
        return Mage::registry('category');
    }

    /**
     * @return TM_NavigationPro_Model_Menu
     */
    public function getMenu()
    {
        return Mage::registry('menu');
    }

    /**
     * @return TM_NavigationPro_Model_Menu
     */
    public function getRootMenu()
    {
        if (!$rootMenu = Mage::registry('root_menu')) {
            $rootMenu = $this->getMenu();
        }
        return $rootMenu;
    }

    public function getFieldName()
    {
        return $this->_name;
    }

    public function getFieldId()
    {
        return $this->_id;
    }
}
