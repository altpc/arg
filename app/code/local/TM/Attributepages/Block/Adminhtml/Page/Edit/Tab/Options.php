<?php

class TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Options
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Set grid params
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('excluded_option_grid');
        $this->setDefaultSort('value');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        if ($this->getPage()->getId()) {
            $this->setDefaultFilter(array('show_excluded_options' => '0'));
        }
    }

    /**
     * Retirve currently edited page model
     *
     * @return TM_Attributepages_Model_Entity
     */
    public function getPage()
    {
        return Mage::registry('attributepages_page');
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Options
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'show_excluded_options') {
            $optionIds = $this->_getExcludedOptions();
            if (empty($optionIds)) {
                $optionIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.option_id', array('in' => $optionIds));
            } else {
                if ($optionIds) {
                    $this->getCollection()->addFieldToFilter('main_table.option_id', array('nin' => $optionIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->getPage()->getRelatedOptions();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $options  = $this->getCollection();
        $entities = Mage::getResourceModel('attributepages/entity_collection')
            ->addOptionOnlyFilter()
            ->addFieldToFilter('option_id', array('in' => $options->getColumnValues('option_id')))
            ->addStoreFilter(Mage::app()->getStore())
            ->load();

        foreach ($options as $option) {
            $entity = $entities->getItemByColumnValue('option_id', $option->getOptionId());
            if ($entity) {
                $option->addData($entity->getData());
            } else {
                $identifier = $option->getValue();
                if (function_exists('mb_strtolower')) {
                    $identifier = mb_strtolower($identifier, 'UTF-8');
                }
                $option->setIdentifier($identifier);
            }
        }

        return parent::_afterLoadCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('show_excluded_options', array(
            'header_css_class' => 'a-center',
            'header' => Mage::helper('attributepages')->__('Exclude from Display'),
            'type'   => 'checkbox',
            'name'   => 'show_excluded_options',
            'values' => $this->_getExcludedOptions(),
            'align'  => 'center',
            'index'  => 'option_id'
        ));

        $this->addColumn('option_id', array(
            'header'   => Mage::helper('catalog')->__('ID'),
            'sortable' => true,
            'width'    => 60,
            'index'    => 'option_id'
        ));

        $this->addColumn('value', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'index'  => 'value'
        ));

        $this->addColumn('identifier', array(
            'header'   => Mage::helper('cms')->__('URL Key'),
            'width'    => 200,
            'sortable' => false,
            'filter'   => false,
            'renderer' => 'attributepages/adminhtml_page_edit_tab_options_renderer_identifier'
        ));

        $this->addColumn('image', array(
            'header' => Mage::helper('catalog')->__('Image'),
            'align'  => 'center',
            'width'     => 220,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'attributepages/adminhtml_page_edit_tab_options_renderer_image'
        ));

        $this->addColumn('thumbnail', array(
            'header' => Mage::helper('catalog')->__('Thumbnail'),
            'align'  => 'center',
            'width'     => 220,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'attributepages/adminhtml_page_edit_tab_options_renderer_thumbnail'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/optionsGrid', array('_current' => true));
    }

    /**
     * Retrieve excluded options
     *
     * @return array
     */
    protected function _getExcludedOptions()
    {
        $options = $this->getOptionsExcluded();
        if (!is_array($options)) {
            $options = $this->getExcludedOptions();
        }
        return $options;
    }

    /**
     * Retrieve excluded options
     *
     * @return array
     */
    public function getExcludedOptions()
    {
        return $this->getPage()->getExcludedOptionIdsArray();
    }

    public function getTabLabel()
    {
        return Mage::helper('catalog')->__('Options');
    }

    public function getTabTitle()
    {
        return Mage::helper('catalog')->__('Options');
    }

    public function canShowTab()
    {
        return (bool) $this->getPage()->getAttributeId();
    }

    public function isHidden()
    {
        return !(bool) $this->getPage()->getAttributeId();
    }

    public function getTabUrl()
    {
        return $this->getUrl('*/*/options', array('_current' => true));
    }

    public function getTabClass()
    {
        return 'ajax';
    }

    public function getSkipGenerateContent()
    {
        return true;
    }
}
