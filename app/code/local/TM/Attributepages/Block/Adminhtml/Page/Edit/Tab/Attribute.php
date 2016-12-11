<?php

class TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Attribute
    extends TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareLayout()
    {
        $this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('catalog')->__('Continue'),
                    'onclick' => 'setAttributeToUse(\''.$this->getContinueUrl().'\',\'attribute_id\')',
                    'class'   => 'save'
                ))
        );
        return parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('attribute', array(
            'legend' => Mage::helper('attributepages')->__('Select Attribute')
        ));

        // get the attributes with options avaialble only
        $attributes = Mage::getResourceModel('attributepages/catalog_product_attribute_collection')
            ->setFrontendInputTypeFilter(array('select', 'multiselect'))
            ->addOrder('frontend_label', 'ASC')
            ->load();

        $oldIds = $attributes->getAllIds();
        $options = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->addFieldToFilter('attribute_id', array('in' => $oldIds));
        $options->getSelect()->group('attribute_id');
        $newIds = $options->getColumnValues('attribute_id');
        $idsToRemove = array_diff($oldIds, $newIds);

        foreach ($idsToRemove as $idToRemove) {
            $attributes->removeItemByKey($idToRemove);
        }
        // end of attributes retrieving

        if (!$attributes->count()) {
            $fieldset->addField('req_text', 'note', array(
                'text' => '<ul class="messages"><li class="notice-msg"><ul><li>'
                        .  $this->__('Attributes with available options are not found.')
                        . '</li></ul></li></ul>'
            ));
        } else {
            $fieldset->addField('attribute_id', 'select', array(
                'label'    => Mage::helper('attributepages')->__('Attribute'),
                'title'    => Mage::helper('attributepages')->__('Attribute'),
                'required' => true,
                'name'     => 'attribute_id',
                'values'   => $attributes->toOptionArray()
            ));
            $fieldset->addField('continue_button', 'note', array(
                'text' => $this->getChildHtml('continue_button'),
            ));
        }

        $this->setForm($form);
    }

    public function getContinueUrl()
    {
        return $this->getUrl('*/*/new', array(
            '_current'     => true,
            'attribute_id' => '{{attribute_id}}'
        ));
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('attributepages')->__('Attribute');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('attributepages')->__('Attribute');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return !(bool) $this->getPage()->getAttributeId();
    }
}
