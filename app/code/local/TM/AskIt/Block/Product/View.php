<?php
class TM_Askit_Block_Product_View extends Mage_Catalog_Block_Product_View
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $block = $this->getLayout()->getBlock('askit');
        $pageVarName = $block->getPageVarName();
        if (empty($pageVarName)) {
            $pageVarName = 'askit_page';
        }
        $page = $this->getRequest()->getParam($pageVarName, 1);
        $title = '';
        if ($page > 1) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Force product view page behave like without options
     *
     * @return false
     */
    public function hasOptions()
    {
        return false;
    }
}
