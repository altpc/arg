<?php
class TM_SoldTogether_Block_Adminhtml_Customer_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('soldtogether_data');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setHtmlIdPrefix('customer_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('soldtogether')->__('Edit Information'), 'class' => 'fieldset-wide'));

        if ($model->getRelationId()) {
            $fieldset->addField('relation_id', 'hidden', array(
                'name' => 'relation_id',
            ));
        }

        $urlModel = Mage::getModel('adminhtml/url');
        $fieldset->addField('product_name', 'link', array(
            'name'      => 'product_name',
            'label'     => Mage::helper('soldtogether')->__('Product name'),
            'title'     => Mage::helper('soldtogether')->__('Product name'),
            'required'  => false,
            'href'      => $urlModel->getUrl(
                'adminhtml/catalog_product/edit/id/' . $model->getProductId()
             ),
            'onclick'   => 'window.open(this.href);return false;'
        ));

        $fieldset->addField('related_product_name', 'link', array(
            'name'      => 'related_product_name',
            'label'     => Mage::helper('soldtogether')->__('Related product name'),
            'title'     => Mage::helper('soldtogether')->__('Related product name'),
            'required'  => false,
            'href'      => $urlModel->getUrl(
                'adminhtml/catalog_product/edit/id/' . $model->getRelatedProductId()
             ),
            'onclick'   => 'window.open(this.href);return false;'
        ));

        $fieldset->addField('weight', 'text', array(
            'name'      => 'weight',
            'label'     => Mage::helper('soldtogether')->__('Weight'),
            'title'     => Mage::helper('soldtogether')->__('Weight')
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _initFormValues()
    {
        $model = Mage::registry('soldtogether_data');
        $collection = $model->getCollection();
        $tablePVarchar = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');
        $tableAttribute = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
        $tableType = Mage::getSingleton('core/resource')->getTableName('eav_entity_type');

        $collection->getSelect()
            ->join(array('cpev1' => $tablePVarchar),
                'cpev1.entity_id = main_table.product_id',
                 array('product_name' => 'value'))
            ->join(array('cpev2' => $tablePVarchar),
                'cpev2.entity_id = main_table.related_product_id',
                array('related_product_name' => 'value'))
            ->join(array('ea' => $tableAttribute),
                'cpev1.attribute_id = ea.attribute_id and cpev2.attribute_id = ea.attribute_id')
            ->join(array('eet' => $tableType),
                'eet.entity_type_id = ea.entity_type_id')
            ->where('ea.attribute_code = ?', 'name')
            ->where('eet.entity_type_code = ?', 'catalog_product')
            ->where('main_table.product_id = ?', $model->getProductId())
            ->where('main_table.related_product_id = ?', $model->getRelatedProductId())
            ->group('main_table.relation_id');

        $items = $collection->getItems();
        $relation = current($items);

        $this->getForm()->setValues(array_merge(
            array(
                'product_name'          => $relation->getProductName(),
                'related_product_name'  => $relation->getRelatedProductName()
            ),
            $model->getData()
        ));

        return $this;
    }
}
