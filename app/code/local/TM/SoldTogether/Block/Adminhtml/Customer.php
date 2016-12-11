<?php
class TM_SoldTogether_Block_Adminhtml_Customer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_customer';
        $this->_blockGroup = 'soldtogether';
        $this->_headerText = Mage::helper('soldtogether')->__('Products Bought by Same Customer Manager');
        parent::__construct();
        $this->_removeButton('add');
        $url = $this->getUrl('adminhtml/soldtogether_customer/reindex');
        $this->_addButton('customer_reindex', array(
            'label'     => Mage::helper('soldtogether')->__('Reindex Data'),
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
                    window.location = '" . $this->getUrl('adminhtml/soldtogether_customer/index') . "'
                }
			}
            sendRequest(1);
                            ",
//            'onclick'   => 'setLocation(\'' . $this->getUrl('soldtogether/adminhtml_customer/reindex') . '\')',
        ));
    }
}