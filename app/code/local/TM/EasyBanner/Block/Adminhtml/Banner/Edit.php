<?php

class TM_EasyBanner_Block_Adminhtml_Banner_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'easybanner';
        $this->_controller = 'adminhtml_banner';

        $this->_updateButton('save', 'label', Mage::helper('easybanner')->__('Save Banner'));

        $objId = $this->getRequest()->getParam($this->_objectId);
        if (!empty($objId)) {
            $this->_updateButton('delete', 'label', Mage::helper('easybanner')->__('Delete Banner'));
            $this->_addButton('clear_statistic', array(
                'label'   => Mage::helper('easybanner')->__('Clear Statistics'),
                'onclick' => 'clearStatistics()',
                'class'   => 'delete'
            ));
            $this->_addButton('duplicate', array(
                'label'   => Mage::helper('catalog')->__('Duplicate'),
                'onclick' => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
                'class'   => 'add'
            ));
        }
        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('easybanner')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            function clearStatistics(){
                new Ajax.Request('".$this->getUrl('*/*/clearStatistics/id/' . Mage::registry('easybanner_banner')->getId())."', {
                    onSuccess: function(){
                        $('banner_display_count').clear();
                        $('banner_clicks_count').clear();
                    }
                });
            }

            function showEasybannerFieldset(el) {
                el.show();
                el.previous().show();
            }

            function hideEasybannerFieldset(el) {
                el.hide();
                el.previous().hide();
            }

            document.observe('dom:loaded', function() {
                var easybannerTypeMapping = {
                    'image': $('banner_image_fieldset'),
                    'html' : $('banner_html_fieldset')
                };

                $('banner_mode').observe('change', function() {
                    var show = $('banner_mode').getValue();
                    var hide = (show == 'image' ? 'html' : 'image');

                    showEasybannerFieldset(easybannerTypeMapping[show]);
                    hideEasybannerFieldset(easybannerTypeMapping[hide]);
                });

                var show = $('banner_mode').getValue();
                var hide = (show == 'image' ? 'html' : 'image');
                showEasybannerFieldset(easybannerTypeMapping[show]);
                hideEasybannerFieldset(easybannerTypeMapping[hide]);
            });

            new FormElementDependenceController({
                'banner_retina_support': {
                    'banner_resize_image': 1
                },
                'banner_background_color': {
                    'banner_resize_image': 1
                }
            });
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('easybanner_banner') && Mage::registry('easybanner_banner')->getId()) {
            return Mage::helper('easybanner')->__("Edit Banner '%s'", $this->htmlEscape(Mage::registry('easybanner_banner')->getIdentifier()));
        } else {
            return Mage::helper('easybanner')->__('Add Banner');
        }
    }

    public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', array('_current'=>true));
    }
}
