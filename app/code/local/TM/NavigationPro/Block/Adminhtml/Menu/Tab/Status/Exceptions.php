<?php

class TM_NavigationPro_Block_Adminhtml_Menu_Tab_Status_Exceptions extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tm/navigationpro/menu/edit/status/exceptions.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('add_status_exception_row_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add Exception'),
                    'class' => 'add add-status-exception-row',
                    'id'    => $this->getFieldId() . '_add_status_exception_row_button_{{' . $this->getIdKey() . '}}'
                ))
        );

        $this->setChild('delete_status_exception_row_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete'),
                    'class' => 'delete delete-status-exception-row icon-btn',
                    'id'    => $this->getFieldId() . '_delete_status_exception_row_button'
                ))
        );

        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_status_exception_row_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_status_exception_row_button');
    }

    public function getIsActiveSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{' . $this->getIdKey() . '}}_is_active_exception_{{exception_id}}_is_active',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{' . $this->getIdKey() . '}}][is_active_exception][{{exception_id}}][is_active]')
            ->setOptions(array(
                '1' => Mage::helper('cms')->__('Enabled'),
                '0' => Mage::helper('cms')->__('Disabled')
            ));

        return $select->getHtml();
    }
}
