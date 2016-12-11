<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_System_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'prolabels';
        $this->_controller = 'adminhtml_rules';

        $this->_updateButton('save', 'label', Mage::helper('prolabels')->__('Save Label'));

        $objId = $this->getRequest()->getParam($this->_objectId);

            $this->_updateButton('delete', 'label', Mage::helper('prolabels')->__('Delete Label'));
            $this->_addButton('saveandcontinue', array(
                'label'   => Mage::helper('prolabels')->__('Save And Continue'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ), -100);

            $this->_formScripts[] = "
                function saveAndContinueEdit(){
                    editForm.submit($('edit_form').action+'back/edit/');
                }
            ";

            // $url = Mage::helper("adminhtml")->getUrl("*/*/applySystemRule");
            // $urlSuccess = Mage::helper("adminhtml")->getUrl("*/*/index");
            // $this->_addButton('reindex', array(
                // 'label'     => Mage::helper('prolabels')->__('Apply'),
                // 'class'   => 'save',
                // 'onclick'   => "
                // function sendRequest(clearSession) {
                    // var params = $('edit_form').serialize(true);
                    // params['clear_session'] = clearSession;
                    // new Ajax.Request('".$url."', {
                        // method: 'post',
                        // parameters: params,
                        // onSuccess: showResponse
                    // });
                // }
// 
                // function showResponse(response) {
                    // var response = response.responseText.evalJSON();
                    // if (!response.completed) {
                        // sendRequest(0);
                        // var imageSrc = $('loading_mask_loader').select('img')[0].src;
                        // $('loading_mask_loader').innerHTML = '<img src=\'' + imageSrc + '\'/><br/>' + response.message;
                    // } else {
                        // window.location = '" . $urlSuccess . "'
                    // }
                // }
                // sendRequest(1);
                                // ",
    // //            'onclick'   => 'setLocation(\'' . $this->getUrl('soldtogether/adminhtml_customer/reindex') . '\')',
            // ));
    }

    public function getHeaderText()
    {
        return Mage::helper('prolabels')->__('Add Multi Store Label');
    }

}
