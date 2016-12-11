<?php

class TM_NavigationPro_Block_Adminhtml_Menu_Edit_Form extends Mage_Adminhtml_Block_Template
// Mage_Adminhtml_Block_Catalog_Category_Abstract
{
    /**
     * Additional buttons on category page
     *
     * @var array
     */
    protected $_additionalButtons = array();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tm/navigationpro/menu/edit/form.phtml');
    }

    public function getCategory()
    {
        return Mage::registry('category');
    }

    public function getMenu()
    {
        return Mage::registry('menu');
    }

    public function getRootMenu()
    {
        if (!$rootMenu = Mage::registry('root_menu')) {
            $rootMenu = $this->getMenu();
        }
        return $rootMenu;
    }

    protected function _prepareLayout()
    {
//        $category = $this->getCategory();
//        $categoryId = (int) $category->getId(); // 0 when we create category, otherwise some value for editing category

        $this->setChild('tabs',
            $this->getLayout()->createBlock('navigationpro/adminhtml_menu_tabs', 'tabs')
        );

        // Save button
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('adminhtml')->__('Save'),
                    'onclick' => "menuSubmit('" . $this->getSaveUrl() . "', true)",
                    'class'   => 'save'
                ))
        );

        // Delete button
        if ($this->getMenu()->getId() && !$this->getMenu()->getRootMenuId()) {
            $this->setChild('delete_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'   => Mage::helper('adminhtml')->__('Delete'),
                        'onclick' => "menuDelete('" . $this->getUrl('*/*/delete', array('_current' => true)) . "', true, {$this->getMenu()->getId()})",
                        'class'   => 'delete'
                    ))
            );
        }

        return parent::_prepareLayout();
    }

    public function getHeaderId()
    {
        if ($this->getCategory()) {
            $id = $this->getCategory()->getId();
        } elseif ($this->getMenu()->getId()) {
            $id = $this->getMenu()->getId();
        } else {
            $id = null;
        }
        return $id;
    }

    public function getSaveUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/save', $params);
    }

    public function getStoreConfigurationUrl()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $params = array();
//        $params = array('section'=>'catalog');
        if ($storeId) {
            $store = Mage::app()->getStore($storeId);
            $params['website'] = $store->getWebsite()->getCode();
            $params['store']   = $store->getCode();
        }
        return $this->getUrl('*/system_store', $params);
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Retrieve additional buttons html
     *
     * @return string
     */
    public function getAdditionalButtonsHtml()
    {
        $html = '';
        foreach ($this->_additionalButtons as $childName) {
            $html .= $this->getChildHtml($childName);
        }
        return $html;
    }

    /**
     * Add additional button
     *
     * @param string $alias
     * @param array $config
     * @return Mage_Adminhtml_Block_Catalog_Category_Edit_Form
     */
    public function addAdditionalButton($alias, $config)
    {
        if (isset($config['name'])) {
            $config['element_name'] = $config['name'];
        }
        $this->setChild($alias . '_button',
                        $this->getLayout()->createBlock('adminhtml/widget_button')->addData($config));
        $this->_additionalButtons[$alias] = $alias . '_button';
        return $this;
    }

    /**
     * Remove additional button
     *
     * @param string $alias
     * @return Mage_Adminhtml_Block_Catalog_Category_Edit_Form
     */
    public function removeAdditionalButton($alias)
    {
        if (isset($this->_additionalButtons[$alias])) {
            $this->unsetChild($this->_additionalButtons[$alias]);
            unset($this->_additionalButtons[$alias]);
        }

        return $this;
    }

    public function getTabsHtml()
    {
        return $this->getChildHtml('tabs');
    }

    public function getHeader()
    {
        if ($this->getCategory()) {
            $header = $this->getCategory()->getName();
        } elseif ($this->getMenu()->getId()) {
            $header = $this->getMenu()->getName();
        } else {
            $header = Mage::helper('navigationpro')->__('New Menu');
        }
        return $header;
    }

    public function getDeleteUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/delete', $params);
    }

    public function isAjax()
    {
        return Mage::app()->getRequest()->isXmlHttpRequest() || Mage::app()->getRequest()->getParam('isAjax');
    }
}
