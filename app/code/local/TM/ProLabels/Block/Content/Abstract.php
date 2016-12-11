<?php

abstract class TM_ProLabels_Block_Content_Abstract
    extends Mage_Core_Block_Template
{

    protected $mode = '';

    public function getMediaDir()
    {
        return
            rtrim(Mage::getBaseUrl('media'), DS)
            . DS
            . 'prolabel'
            . DS;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function getLabelText($label)
    {
        $product = $this->getProduct();
        if (!$product) {
            return false;
        }
        $helper = Mage::helper('prolabels');
        return $this->escapeHtml(
                $helper->_getText($product, $this->mode, $label)
            );
    }

    public function validateContentLabel($label)
    {
        $helper = Mage::helper('prolabels');
        if ('1' == $label['rules_id']) {
            return $helper->_isOnSale(
                    $this->getProduct(), $this->mode, $label
                );
        } else if ('2' == $label['rules_id']) {
            return $helper->_canShowQuantity(
                    $this->getProduct(), $this->mode, $label
                );
        } else if ('3' == $label['rules_id']) {
            return $helper->checkNewDate($this->getProduct());
        }
    }

    public function renderContentLabel($label, $cssClass = 'tt-gplus') {
        $tooltipText = $this->getLabelText($label);

        $image = sprintf(
            '<img src="%s" alt="%s" />',
            $this->getMediaDir() . $label[$this->mode . '_image'],
            $tooltipText
        );

        $tooltip = '';
        if ($tooltipText != '') {
            $tooltip = sprintf('<span class="tooltip-label">%s</span>', $tooltipText);
        }

        $aHref = '';
        $aTitle = '';
        if ($label['product_custom_url']) {
            $aHref = sprintf('href="%s" target="_blank"', $label[$this->mode . '_custom_url']);
        }
        if ($tooltipText != '') {
            $aTitle = sprintf('title="%s"', $tooltipText);
        }

        return sprintf(
                '<a class="%5$s" %1$s %2$s>%3$s%4$s</a>',
                $aHref,
                $aTitle,
                $image,
                $tooltip,
                $cssClass
            );
    }

    public function getContentLabels()
    {
        $result = array();
        $helper = Mage::helper('prolabels');
        if ($helper->isMobileMode() && Mage::getStoreConfig("prolabels/general/mobile")) {
            return array();
        }
        $contentLabels = $helper->getRegistryContentLabels(
                $this->getProduct()->getId(),
                $this->mode
            );

        foreach ($contentLabels as $label) {
            if (Mage::getStoreConfig("prolabels/general/customer_group")) {
                $labelCustomerGroups = unserialize($label['customer_group']);
                $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                if ($labelCustomerGroups) {
                    if (!in_array($roleId, $labelCustomerGroups)) {
                        continue;
                    }
                }
            }
            if (array_key_exists('system_id', $label)) {
                if ($this->validateContentLabel($label)) {
                    if ($helper->checkSystemLabelStore($label['system_id'], $this->mode)) {
                        $result[] = $label;
                    }
                }
            } else {
                if ($helper->checkLabelStore($label['rules_id'], $this->mode)) {
                    $result[] = $label;
                }
            }
        }

        return $result;
    }

    public function getTooltipConfig()
    {
        return json_encode(array(
                'background' => Mage::getStoreConfig('prolabels/tooltip/background'),
                'color' => Mage::getStoreConfig('prolabels/tooltip/color')
            ));
    }

}
