<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_Rules extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_rules';
        $this->_blockGroup = 'prolabels';
        $this->_headerText = Mage::helper('prolabels')->__('Manage Labels');
        $this->_addButtonLabel = Mage::helper('prolabels')->__('Add Label');
//        $this->_addButton('reindex', array(
//            'label'   => Mage::helper('affiliatesuite')->__('Reindex Labels'),
//            'onclick' => 'setLocation(\'' . Mage::helper("adminhtml")->getUrl("*/*/applyRules") . '\')',
//            'class'   => 'save'
//        ));

        parent::__construct();

        $url = Mage::helper("adminhtml")->getUrl("*/*/applyRules");
        $urlSuccess = Mage::helper("adminhtml")->getUrl("*/*/applyUserRules");
        $this->_addButton('reindex', array(
            'label'     => Mage::helper('prolabels')->__('Reindex Labels'),
            'onclick'   => "
            function sendRequest(clearSession) {
                new Ajax.Request('".$url."', {
                    method: 'post',
                    parameters: {
                        clear_session: clearSession
                    },
                    onSuccess: showResponse
                    });
                }

            function showResponse(response) {
                var response = response.responseText.evalJSON();
                if (!response.completed) {
                    sendRequest(0);
                    var imageSrc = $('loading_mask_loader').select('img')[0].src;
                    $('loading_mask_loader').innerHTML = '<img src=\'' + imageSrc + '\'/><br/>' + response.message;
                } else {
                    window.location = '" . $urlSuccess . "'
                }
            }
            sendRequest(1);
                            ",
//            'onclick'   => 'setLocation(\'' . $this->getUrl('soldtogether/adminhtml_customer/reindex') . '\')',
        ));
    }
}