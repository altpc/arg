<?php

class TM_EasyBanner_Model_Rule_Condition_Banner extends Mage_Rule_Model_Condition_Abstract
{
    /**
     * Load attribute options
     *
     * @return TM_EasyBanner_Model_Rule_Condition_Banner
     */
    public function loadAttributeOptions()
    {
        $attributes = array(
            'category_ids'      => Mage::helper('easybanner')->__('Category'),
            'product_ids'       => Mage::helper('easybanner')->__('Product'),
            'subtotal_excl'     => Mage::helper('easybanner')->__('Subtotal (Excl.Tax)'),
            'subtotal_incl'     => Mage::helper('easybanner')->__('Subtotal (Incl.Tax)'),
            'date'              => Mage::helper('easybanner')->__('Date'),
            'time'              => Mage::helper('easybanner')->__('Time'),
            'handle'            => Mage::helper('easybanner')->__('Page'),
            'clicks_count'      => Mage::helper('easybanner')->__('Clicks Count'),
            'display_count'     => Mage::helper('easybanner')->__('Display Count'),
            'customer_group'    => Mage::helper('easybanner')->__('Customer Group'),
            'display_count_per_customer' => Mage::helper('easybanner')->__('Lightbox/Awesomebar: Display Count per Customer'),
            'browsing_time'     => Mage::helper('easybanner')->__('Lightbox/Awesomebar: Customer browsing time (seconds)'),
            'inactivity_time'   => Mage::helper('easybanner')->__('Lightbox/Awesomebar: Customer inactivity time (seconds)'),
            'activity_time'     => Mage::helper('easybanner')->__('Lightbox/Awesomebar: Customer activity time (seconds)'),
            'scroll_offset'     => Mage::helper('easybanner')->__('Lightbox/Awesomebar: Scroll offset')
        );
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Retrieve value by option
     *
     * @param mixed $option
     * @return string
     */
    public function getValueOption($option=null)
    {
        return $this->getData('value_option'.(!is_null($option) ? '/'.$option : ''));
    }

    public function getValue()
    {
        if ($this->getInputType()=='time' && !$this->getIsTimeValueParsed()) {
            if (null === $this->getData('value')) {
                $this->setValue('00:00');
                $this->setIsTimeValueParsed(true);
            }
        }
        return parent::getValue();
    }

    /**
     * Retrieve after element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'product_ids': case 'category_ids':
            case 'handle': case 'customer_group':
                $image = Mage::getDesign()->getSkinUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' . $image . '" alt="" class="v-middle rule-chooser-trigger" title="' . Mage::helper('rule')->__('Open Chooser') . '" /></a>';
        }
        return $html;
    }

    /**
     * Retrieve attribute element
     *
     * @return Varien_Form_Element_Abstract
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'category_ids': case 'product_ids':
            case 'customer_group': case 'handle':
                return 'grid';
            case 'date':
                return 'date';
            case 'time':
                return 'time';
            case 'inactivity_time':
            case 'activity_time':
            case 'browsing_time':
            case 'scroll_offset':
            case 'subtotal_excl':
            case 'subtotal_incl':
                return 'interval';
            case 'display_count':
            case 'clicks_count':
            case 'display_count_per_customer':
                return 'increment';
            default:
                return 'string';
        }
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'date':
                return 'date';
            case 'time':
                return 'text';
            default:
                return 'text';
        }
    }

    /**
     * Retrieve value element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();
        switch ($this->getAttribute()) {
            case 'date':
                $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                break;
        }
        return $element;
    }

    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'product_ids': case 'category_ids':
            case 'handle': case 'customer_group':
                $url = '*/*/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/'.$this->getJsFormObject();
                }
                break;
        }
        return $url !== false ? Mage::helper('adminhtml')->getUrl($url) : '';
    }

    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        if (in_array($this->getAttribute(),
            array('product_ids', 'category_ids', 'date', 'customer_group', 'handle'))) {

            return true;
        }
        return false;
    }

    public function getOperatorSelectOptions()
    {
        // if ($this->getAttribute() === 'category_ids') {
        //     $type = 'multiselect';
        // } else {
            $type = $this->getInputType();
        // }
        $opt = array();
        $operatorByType = $this->getOperatorByInputType();
        foreach ($this->getOperatorOption() as $k=>$v) {
            if (!$operatorByType || in_array($k, $operatorByType[$type])) {
                $opt[] = array('value'=>$k, 'label'=>$v);
            }
        }
        return $opt;
    }

    /**
     * Issue 18371 fix
     */
    public function getOperatorElement()
    {
        if (null === $this->getOperator()) {
            $options = $this->getOperatorOption();
            $operator = $this->getOperatorByInputType($this->getInputType());
            $operator = current($operator);
            if (isset($options[$operator])) {
                $this->setOperator($operator);
            } else {
                foreach ($options as $k => $v) {
                    $this->setOperator($k);
                    break;
                }
            }
        }
        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__operator', 'select', array(
            'name'=>'rule['.$this->getPrefix().']['.$this->getId().'][operator]',
            'values'=>$this->getOperatorSelectOptions(),
            'value'=>$this->getOperator(),
            'value_name'=>$this->getOperatorName(),
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    /**
     * Add increment, time operators
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '=='  => Mage::helper('rule')->__('is'),
            '!='  => Mage::helper('rule')->__('is not'),
            '>='  => Mage::helper('rule')->__('equals or greater than'),
            '<='  => Mage::helper('rule')->__('equals or less than'),
            '>'   => Mage::helper('rule')->__('greater than'),
            '<'   => Mage::helper('rule')->__('less than'),
            '{}'  => Mage::helper('rule')->__('contains'),
            '!{}' => Mage::helper('rule')->__('does not contain'),
            '()'  => Mage::helper('rule')->__('is one of'),
            '!()' => Mage::helper('rule')->__('is not one of'),
        ));
        $this->setOperatorByInputType(array(
            'string' => array('==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'),
            'numeric' => array('==', '!=', '>=', '>', '<=', '<', '()', '!()'),
            'increment' => array('<'),
            'interval' =>  array('<', '>'),
            'time' => array('==', '>=', '<='),
            'date' => array('==', '>=', '<='),
            'select' => array('==', '!='),
            'multiselect' => array('==', '!=', '{}', '!{}'),
            'grid' => array('()', '!()'),
        ));
        return $this;
    }
}
