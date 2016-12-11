<?php

class TM_NavigationPro_Block_Adminhtml_Menu_Tab_Siblings_Sibling extends Mage_Adminhtml_Block_Widget
{
    protected $_name = 'siblings';
    protected $_id = 'sibling';
    protected $_values;
    protected $_itemCount = 1;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tm/navigationpro/menu/edit/siblings/sibling.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('adminhtml')->__('Delete'),
                    'class' => 'delete delete-sibling'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getAddButtonId()
    {
        return $this->getLayout()
            ->getBlock('menu.siblings')
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
                'id' => $this->getFieldId().'_{{sibling_id}}_is_active',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{sibling_id}}][is_active]')
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
                    'id_key'     => 'sibling_id',
                    'field_id'   => $this->getFieldId(),
                    'field_name' => $this->getFieldName()
                )
            )
            ->toHtml();
    }

    public function getSiblingsData()
    {
        /**
         * @var TM_NavigationPro_Model_Resource_Sibling_Collection
         */
        $collection = $this->getMenu()->getSiblingsCollection()
            ->setOrder('sort_order', 'DESC'); // need to sort DESC to match js insert on top logic

        $collection->addContentToResult($this->getMenu()->getStoreId());
        foreach ($collection as $sibling) {
            $this->setItemCount($sibling->getId());
            $sibling->setItemCount($this->getItemCount());

             $textFields = array(
                'content', 'dropdown_content', 'sort_order', 'dropdown_styles'
            );
            foreach ($textFields as $field) {
                $sibling->setData(
                    $field, $this->escapeHtml($sibling->getData($field))
                );
            }
        }
        return $collection;
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
