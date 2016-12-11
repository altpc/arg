<?php

class TM_NavigationPro_Block_Adminhtml_Catalog_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    protected $_menus = array();

    /**
     * Overriden to change the button labels
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->unsetChild('add_sub_button')
            ->unsetChild('add_root_button');

        $addUrl = $this->getUrl("*/*/add", array(
            '_current'=>true,
            'id'=>null,
            '_query' => false
        ));

        $this->setChild('add_menu_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('navigationpro')->__('Add Menu'),
                    'onclick'   => "addNew('".$addUrl."', false)",
                    'class'     => 'add',
                    'id'        => 'add_menu_button'
                ))
        );
    }

    public function getMenu()
    {
        return Mage::registry('menu');
    }

    public function getRootMenu()
    {
        if (!$rootMenu = Mage::registry('root_menu')) {
            $rootMenu = $this->getMenu();
        }
        return $rootMenu;
    }

    /**
     * Overriden to fix the url to categoriesJson action
     */
    public function getLoadTreeUrl($expanded=null)
    {
        $params = array('_current'=>true, 'id'=>null,'store'=>null);
        if (
            (is_null($expanded) && Mage::getSingleton('admin/session')->getIsTreeWasExpanded())
            || $expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('*/*/categoriesJson', $params);
    }

    public function getEditUrl()
    {
        return $this->getUrl("*/*/edit", array('_current'=>true, 'store'=>null, '_query'=>false, 'id'=>null, 'parent'=>null));
    }

    public function getSwitchTreeUrl()
    {
        return $this->getUrl("*/*/tree", array('_current'=>true, 'store'=>null, '_query'=>false, 'id'=>null, 'parent'=>null));
    }

    public function getAddMenuButtonHtml()
    {
        return $this->getChildHtml('add_menu_button');
    }

    public function getTree($parenNodeCategory = null)
    {
        return $this->getMenus($this->getAbsoluteRoot($parenNodeCategory));
    }

    /**
     * Root tree renedering. Add categories to every menu.
     */
    public function getTreeJson($parenNodeCategory = null)
    {
        if (null === $parenNodeCategory) {
            $rootArray = $this->getMenus($this->getRoot());
        } else {
            $rootArray = $this->_getMenuNodeJson($this->getRoot($parenNodeCategory), 0, $this->getRootMenu());
            $rootArray = isset($rootArray['children']) ? $rootArray['children'] : array();
        }
        return Mage::helper('core')->jsonEncode($rootArray);
    }

    public function getMenus($node)
    {
        $this->_withProductCount = true;
        $menus      = array();
        $collection = Mage::getModel('navigationpro/menu')->getCollection()
            ->addFieldToFilter('root_menu_id', array('is' => new Zend_Db_Expr('NULL')));

        foreach ($collection as $menu) {
            $rootArray = $this->_getMenuNodeJson($node, 0, $menu);

            $menus[] = array(
                'text'      => $menu->getName(),
                'id'        => 'menu_' . $menu->getId(),
                'menu_id'   => $menu->getId(),
                'path'      => '1/menu_' . $menu->getId(),
                'is_active' => (int) $menu->getIsActive(),
                'cls'       => 'folder ' . ($menu->getIsActive() ? 'active-category' : 'no-active-category'),
                'allowDrop' => false,
                'allowDrag' => false,
                'children'  => isset($rootArray['children']) ? $rootArray['children'] : array()
            );
        }
        return $menus;
    }

    /**
     * Get JSON of array of categories, that are breadcrumbs for specified category path
     *
     * @param string $path
     * @param string $javascriptVarName
     * @return string
     */
    public function getBreadcrumbsJavascript($path, $javascriptVarName)
    {
        if (empty($path)) {
            return '';
        }

        $categoryPath = preg_replace("/menu_\d+\//", '', $path);

        if (empty($categoryPath)) {
            $categories = $this->getMenus($this->getRoot());
        } else {
            $categories = Mage::getResourceSingleton('catalog/category_tree')
                ->setStoreId($this->getStore()->getId())->loadBreadcrumbsArray($categoryPath);

            $rootMenu = $this->getRootMenu();
            $rootPath = '1/menu_' . $rootMenu->getId();

            foreach ($categories as $key => $category) {
                $categories[$key] = $this->_getMenuNodeJson($category, 0, $rootMenu);
            }

            $categories[] = array(
                'text'      => $rootMenu->getName(),
                'id'        => 'menu_' . $rootMenu->getId(),
                'menu_id'   => $rootMenu->getId(), // only root menus has this field
                'store'     => (int) $this->getStore()->getId(),
                'path'      => $rootPath,
                'is_active' => (int) $rootMenu->getIsActive(),
                'cls'       => 'folder ' . ($rootMenu->getIsActive() ? 'active-category' : 'no-active-category'),
                'allowDrop' => false,
                'allowDrag' => false
            );
            $this->_withProductCount = false;
        }

        return
            '<script type="text/javascript">'
            . $javascriptVarName . ' = ' . Mage::helper('core')->jsonEncode($categories) . ';'
            . '</script>';
    }

    protected function _getMenu($rootMenuId, $categoryId)
    {
        if (empty($this->_menus)) {
            $collection = Mage::getModel('navigationpro/menu')->getCollection()
                ->addFieldToFilter('root_menu_id', array('notnull' => 1));

            $menus = array();
            foreach ($collection as $menu) {
                $menus[$menu->getRootMenuId()][$menu->getCategoryId()] = $menu;
            }
            $this->_menus = $menus;
        }

        if (!isset($this->_menus[$rootMenuId][$categoryId])) {
            $this->_menus[$rootMenuId][$categoryId] = false;
        }
        return $this->_menus[$rootMenuId][$categoryId];
    }

    protected function _getMenuNodeJson($node, $level = 0, $rootMenu)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Varien_Data_Tree_Node($node, 'entity_id', new Varien_Data_Tree);
        }

        $menu = $this->_getMenu($rootMenu->getId(), $node->getId());

        $item = array();
        $item['text'] = $this->buildNodeName($node);

        //$rootForStores = Mage::getModel('core/store')->getCollection()->loadByCategoryIds(array($node->getEntityId()));
        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id']  = $node->getId();
        $item['store']  = (int) $this->getStore()->getId();

        $rootPath = '1/menu_' . $rootMenu->getId();
        $item['path'] = $rootPath . '/' . preg_replace("/\d+\//", '', $node->getData('path'), 1);

        $item['cls'] = 'folder ';
        if ($node->getIsActive() && ($menu === false || $menu->getIsActive())) {
            $item['cls'] .= 'active-category';
            $item['is_active'] = 1;
        } else {
            $item['cls'] .= 'no-active-category';
            $item['is_active'] = 0;
        }
        //$item['allowDrop'] = ($level<3) ? true : false;
        $allowMove = $this->_isCategoryMoveable($node);
        $item['allowDrop'] = $allowMove;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = $allowMove && (($node->getLevel()==1 && $rootForStores) ? false : true);

        if ((int)$node->getChildrenCount()>0) {
            $item['children'] = array();
        }

        $isParent = $this->_isParentSelectedCategory($node);

        if ($node->hasChildren()) {
            $item['children'] = array();
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getMenuNodeJson($child, $level+1, $rootMenu);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }

    public function getAbsoluteRoot($parentNodeCategory=null, $recursionLevel=3)
    {
        if (!is_null($parentNodeCategory) && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = Mage::registry('absolute_root');
        if (is_null($root)) {
            $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
            $tree = Mage::getResourceSingleton('catalog/category_tree')
                ->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            }
            elseif($root && $root->getId() == Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setName(Mage::helper('catalog')->__('Root'));
            }

            Mage::register('absolute_root', $root);
        }

        return $root;
    }
}
