<?php

class TM_Attributepages_Block_Product_Option extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    const IMAGE_WIDTH  = 30;
    const IMAGE_HEIGHT = 15;

    /**
     * Get template path to render
     *
     * @return string
     */
    public function getTemplate()
    {
        $template = parent::getTemplate();
        if (null === $template) {
            $template = $this->_getData('template');
        }
        if (null === $template) {
            /**
             * @deprecated
             */
            $template = 'tm/attributepages/product/option.phtml';
        }
        return $template;
    }

    /**
     * Retrieve product to use for output
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $product = $this->_getData('product');
        if (null === $product) {
            $product = Mage::registry('current_product');
            $this->setData('product', $product);
        }
        return $product;
    }

    /**
     * Returns attributepage options to render
     *
     * @return array
     */
    public function getOptions()
    {
        // inline call
        if ($this->getProduct() && $this->getAttributeCode()) {
            $attributeCode = $this->getAttributeCode();
            if (!is_array($attributeCode)) {
                $attributeCode = str_replace(' ', '', $attributeCode);
                $attributeCode = explode(',', $attributeCode);
            }
            $isLoaded = false;
            $loaded = $this->getProduct()->getAttributepages();
            if (null !== $loaded) {
                $notLoaded = array_diff($attributeCode, array_keys($loaded));
                if (!$notLoaded) {
                    $isLoaded = true;
                }
            }
            if (!$this->getAttributeToShow()) {
                $this->setAttributeToShow($attributeCode);
            }

            if (!$isLoaded) {
                Mage::helper('attributepages/product')->appendPages(
                    $this->getProduct(), $attributeCode, $this->getParentPageIdentifier()
                );
            }
        }

        if ($this->getProduct() && $this->getProduct()->getAttributepages()) {
            $options = $this->getProduct()->getAttributepages();

            $visible = $this->getAttributeToShow();
            if ($visible) {
                if (!is_array($visible)) {
                    $visible = explode(',', $visible);
                }
                $options = array_intersect_key($options, array_flip($visible));
            }

            $hidden = $this->getAttributeToHide();
            if ($hidden) {
                if (!is_array($hidden)) {
                    $hidden = explode(',', $hidden);
                }
                $options = array_diff_key($options, array_flip($hidden));
            }
            return array_filter($options); // remove nulls
        }
        return array();
    }

    /**
     * Get image width
     *
     * @return int
     */
    public function getWidth()
    {
        $width = $this->_getData('width');
        if (null === $width) {
            return self::IMAGE_WIDTH;
        }
        return $width;
    }

    /**
     * Get image height
     *
     * @return int
     */
    public function getHeight()
    {
        $height = $this->_getData('height');
        if (null === $height) {
            return self::IMAGE_HEIGHT;
        }
        return $height;
    }

    /**
     * Get custom title to the parent page
     *
     * @param  TM_Attributepages_Model_Entity $attributepage
     * @return mixed
     */
    public function getParentPageLinkTitle(TM_Attributepages_Model_Entity $attributepage = null)
    {
        return $this->_getPageData('parent_page_link_title', $attributepage);
    }

    /**
     * @return TM_Attributepages_Helper_Image
     */
    public function getImageHelper()
    {
        $helper = $this->helper('attributepages/image');
        if (null !== $this->getKeepFrame()) {
            $helper->setKeepFrame($this->getKeepFrame());
        }
        return $helper;
    }

    public function getImageType()
    {
        $type = $this->_getData('image_type');
        if (!$type) {
            return 'thumbnail';
        }
        return $type;
    }

    /**
     * Retieve flag, that indicates if the image should be shown
     *
     * @return boolean
     */
    public function getUseImage(TM_Attributepages_Model_Entity $attributepage = null)
    {
        $flag = $this->_getPageData('use_image', $attributepage);
        if (null === $flag) {
            return true;
        }
        return $flag;
    }

    /**
     * Retieve flag, that indicates if the link should be used
     *
     * @return boolean
     */
    public function getUseLink(TM_Attributepages_Model_Entity $attributepage = null)
    {
        $flag = $this->_getPageData('use_link', $attributepage);
        if (null === $flag) {
            return true;
        }
        return $flag;
    }

    /**
     * Retrieve data for particular page
     *
     * @param  string
     * @param  TM_Attributepages_Model_Entity $attributepage
     * @return mixed
     */
    protected function _getPageData($key, TM_Attributepages_Model_Entity $attributepage = null)
    {
        $data = $this->_getData($key);
        if (!$data || null === $attributepage || !is_array($data)) {
            return $data;
        }

        if (!$attributepage->getAttributeCode()
            || !isset($data[$attributepage->getAttributeCode()])) {

            return null;
        }
        return $data[$attributepage->getAttributeCode()];
    }
}
