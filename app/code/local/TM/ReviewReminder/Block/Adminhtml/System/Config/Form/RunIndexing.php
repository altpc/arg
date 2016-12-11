<?php

class TM_ReviewReminder_Block_Adminhtml_System_Config_Form_RunIndexing
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $url  = $this->getUrl('adminhtml/reviewreminder_index/indexOrders');
        return <<<HTML
<tr>
    <td colspan="100">
        <button onclick="indexOrders(0, 0);" class="scalable save" type="button"><span><span><span>{$this->__("Run")}</span></span></span></button>
        <script type="text/javascript">
            function indexOrders(last_processed, processed) {
                if (!$('loading_mask_processed')) {
                    $('loading_mask_loader').insert({
                        bottom: '<span id="loading_mask_processed" style="display: block;">0</span>'
                    });
                }
                new Ajax.Request("$url", {
                    parameters: {
                        last_processed: last_processed,
                        processed: processed,
                        from_date: $('tm_reviewreminder_initial_indexing_from_date').value,
                        from_date_type: $('tm_reviewreminder_initial_indexing_from_date_type').value,
                        stores: $('tm_reviewreminder_initial_indexing_store_view').getValue().join()
                    },
                    onSuccess: function(response) {
                        var response = response.responseText;
                        try {
                            response = response.evalJSON();
                        } catch (e) {
                            alert('{$this->__("An error occured.")}' + response);
                            return;
                        }

                        if (response.error) {
                            alert(response.error);
                            return;
                        }

                        if (!response.finished) {
                            indexOrders(response.last_processed, response.processed);
                            $('loading_mask_processed').update(response.processed);
                        } else {
                            $('loading_mask_processed').remove();
                            var message = '{$this->__("Completed. {count} items was processed")}';
                            alert(message.replace('{count}', response.processed));
                        }
                    },
                    onFailure: function(response) {
                        alert('{$this->__("An error occured.")}' + response.responseText);
                    }
                });
            }
        </script>
    </td>
</tr>
HTML;
    }
}