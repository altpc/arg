<?php

class TM_Attributepages_Block_Adminhtml_Page_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'attributepages';
        $this->_objectId   = 'entity_id';
        $this->_controller = 'adminhtml_page';

        parent::__construct();

        if (!$this->getPage()->getAttributeId()) {
            $this->_removeButton('save');
            $this->_removeButton('delete');
            $this->_removeButton('reset');
            return;
        }

        if ($this->_isAllowedAction('save')) {
            $this->_updateButton('save', 'label', Mage::helper('cms')->__('Save Page'));
            $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit(\''.$this->_getSaveAndContinueUrl().'\')',
                'class'     => 'save',
            ), -100);

            if (Mage::registry('attributepages_page')->getId()) {
                $this->_addButton('duplicate', array(
                    'label'   => Mage::helper('catalog')->__('Duplicate'),
                    'onclick' => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
                    'class'   => 'add'
                ));
            }
        } else {
            $this->_removeButton('save');
        }

        if ($this->_isAllowedAction('delete')) {
            $this->_updateButton('delete', 'label', Mage::helper('cms')->__('Delete Page'));
        } else {
            $this->_removeButton('delete');
        }
    }

    public function getPage()
    {
        return Mage::registry('attributepages_page');
    }

    public function getHeaderText()
    {
        if ($this->getPage()->getId()) {
            return Mage::helper('cms')->__(
                "Edit Page '%s'",
                $this->escapeHtml(Mage::registry('attributepages_page')->getName())
            );
        } elseif ($option = $this->getPage()->getOption()) {
            return $option->getValue();
        } else {
            return Mage::helper('cms')->__('New Page');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        $prefix = 'templates_master/attributepages/attributepages_page/';
        if ($this->getPage()->getOption()) {
            $prefix = 'templates_master/attributepages/attributepages_option/';
        }
        return Mage::getSingleton('admin/session')->isAllowed($prefix . $action);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',
            'active_tab' => '{{tab_id}}'
        ));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current' => true
        ));
    }

    public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', array(
            '_current' => true
        ));
    }

    /**
     * Prepare layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $tabsBlock = $this->getLayout()->getBlock('attributepages_page_edit_tabs');
        if ($tabsBlock) {
            $tabsBlockJsObject = $tabsBlock->getJsObjectName();
            $tabsBlockPrefix   = $tabsBlock->getId() . '_';
        } else {
            $tabsBlockJsObject = 'page_tabsJsTabs';
            $tabsBlockPrefix   = 'page_tabs_';
        }

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            }

            function saveAndContinueEdit(urlTemplate) {
                var tabsIdValue = " . $tabsBlockJsObject . ".activeTab.id;
                var tabsBlockPrefix = '" . $tabsBlockPrefix . "';
                if (tabsIdValue.startsWith(tabsBlockPrefix)) {
                    tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length)
                }
                var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/);
                var url = template.evaluate({tab_id:tabsIdValue});
                editForm.submit(url);
            }

            function setAttributeToUse(urlTemplate, attributeFieldId) {
                var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/);
                setLocation(template.evaluate({
                    attribute_id: \$F(attributeFieldId)
                }));
            }

            if ($('page_image_width')) {
                new FormElementDependenceController({
                    'page_image_width': {
                        'page_listing_mode': 'image'
                    },
                    'page_image_height': {
                        'page_listing_mode': 'image'
                    }
                });
            }
        ";
        return parent::_prepareLayout();
    }
}
