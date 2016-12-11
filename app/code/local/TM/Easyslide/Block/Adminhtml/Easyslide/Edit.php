<?php

class TM_Easyslide_Block_Adminhtml_Easyslide_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'easyslide';
        $this->_controller = 'adminhtml_easyslide';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('easyslide')->__('Save Slider'));
        $this->_updateButton('delete', 'label', Mage::helper('easyslide')->__('Delete Slider'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }

            function showEasyslideFieldset(el) {
                el.show();
                el.previous().show();
            }

            function hideEasyslideFieldset(el) {
                el.hide();
                el.previous().hide();
            }

            document.observe('dom:loaded', function() {
                var easysliderTypeMapping = [
                    $('easyslide_prototype_fieldset'),
                    $('easyslide_nivo_fieldset')
                ];

                $('easyslide_slider_type').observe('change', function() {
                    var show = parseInt(this.getValue());
                    var hide = (show == 0 ? 1 : 0);

                    showEasyslideFieldset(easysliderTypeMapping[show]);
                    hideEasyslideFieldset(easysliderTypeMapping[hide]);
                });

                var show = parseInt($('easyslide_slider_type').getValue());
                var hide = (show == 0 ? 1 : 0);
                showEasyslideFieldset(easysliderTypeMapping[show]);
                hideEasyslideFieldset(easysliderTypeMapping[hide]);
            });
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('slider') && Mage::registry('slider')->getId()) {
            return Mage::helper('easyslide')->__("Edit Slider '%s'", $this->htmlEscape(Mage::registry('slider')->getTitle()));
        } else {
            return Mage::helper('easyslide')->__('Add Slider');
        }
    }

    /**
     * Get form save URL
     *
     * @deprecated
     * @see getFormActionUrl()
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current' => true
        ));
    }
}