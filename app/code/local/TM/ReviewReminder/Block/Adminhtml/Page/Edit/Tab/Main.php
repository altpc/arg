<?php
class TM_ReviewReminder_Block_Adminhtml_Page_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $model = Mage::registry('reminder_data');
        $form  = new Varien_Data_Form();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('tm_reviewreminder')->__('Reminder Information'),
            'class' => 'fieldset-wide'
        ));
        if ($model->getEntityId()) {
            $fieldset->addField('entity_id', 'hidden', array(
                'name'  => 'entity_id'
            ));
        }
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $fieldset->addField('fullname', 'label', array(
            'name'    => 'fullname',
            'label'    => Mage::helper('adminhtml')->__('Customer Name'),
            'title'    => Mage::helper('adminhtml')->__('Customer Name')
        ));
        $fieldset->addField('customer_email', 'label', array(
            'name'     => 'customer_email',
            'label'    => Mage::helper('sales')->__('Customer Email'),
            'title'    => Mage::helper('sales')->__('Customer Email')
        ));
        $fieldset->addField('order_date', 'label', array(
            'name'     => 'order_date',
            'label'    => Mage::helper('sales')->__('Order Date'),
            'title'    => Mage::helper('sales')->__('Order Date'),
            'format'   => Mage::app()->getLocale()->getDateFormatWithLongYear()
        ));
        $fieldset->addField('products', 'label', array(
            'name'    => 'products',
            'label'    => Mage::helper('adminhtml')->__('Products'),
            'title'    => Mage::helper('adminhtml')->__('Products')
        ));
        $fieldset->addField('status', 'select', array(
            'name'     => 'status',
            'label'    => Mage::helper('cms')->__('Status'),
            'title'    => Mage::helper('cms')->__('Status'),
            'options'  => $model->getAvailableStatuses(),
            'disabled' => $isElementDisabled,
            'required' => true
        ));
        $form->addValues($model->getData());
        $form->setFieldNameSuffix('reviewreminder');
        $this->setForm($form);
        return parent::_prepareForm();
    }
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('tm_reviewreminder')->__('Reminder Information');
    }
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('tm_reviewreminder')->__('Reminder Information');
    }
    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tm_reviewreminder/' . $action);
    }
}
