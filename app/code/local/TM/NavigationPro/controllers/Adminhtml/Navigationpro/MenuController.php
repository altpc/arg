<?php

class TM_NavigationPro_Adminhtml_Navigationpro_MenuController extends
    Mage_Adminhtml_Controller_Action
{
    /**
     * If categoryId is supplied, menuId is used as root_menu_id
     * Register menu, root_menu and category into registry
     *
     * @param int $menuId menu_id or root_menu_id
     * @param int $categoryId
     * @return TM_NavigationPro_Model_Menu
     */
    protected function _initMenu($menuId, $categoryId = null)
    {
        if (null !== $categoryId) {
            $menu = Mage::getModel('navigationpro/menu')
                ->getCollection()
                ->addFieldToFilter('category_id', $categoryId)
                ->addFieldToFilter('root_menu_id', $menuId)
                ->getFirstItem();

            if (!$menu->getId()) {
                $menu->setCategoryId($categoryId)
                    ->setRootMenuId($menuId)
                    ->setColumnsMode(TM_NavigationPro_Model_Menu::COLUMNS_MODE_DEFAULT);
            }

            $rootMenu = Mage::getModel('navigationpro/menu')->load($menuId);
            Mage::register('root_menu', $rootMenu);

            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('category', $category);
            Mage::register('current_category', $category);
        } else {
            $menu = Mage::getModel('navigationpro/menu')->load($menuId);
        }

        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
        }

        $storeId = (int) $this->getRequest()->getParam('store');
        $menu->setStoreId($storeId);

        Mage::register('menu', $menu);

        return $menu;
    }

    public function indexAction()
    {
        $this->_forward('edit');
    }

    public function addAction()
    {
        Mage::getSingleton('admin/session')->unsActiveTabId();
        $this->_forward('edit');
    }

    public function editAction()
    {
        $categoryId = $this->getRequest()->getParam('category_id');

        if ($categoryId) {
            $menuId = $this->getRequest()->getParam('root_menu_id');
        } else {
            $menuId = $this->getRequest()->getParam('menu_id');
        }

        $menu = $this->_initMenu($menuId, $categoryId);

        if ($this->getRequest()->getQuery('isAjax')) {
            $breadcrumbsPath = '';
            if (!$rootMenu = Mage::registry('root_menu')) {
                $rootMenu = $menu;
            }
            if ($rootMenu->getId()) {
                $breadcrumbsPath .= 'menu_' . $rootMenu->getId() . '/';
            }
            if (Mage::registry('category')) {
                $breadcrumbsPath .= Mage::registry('category')->getPath();
            }

//            Mage::getSingleton('admin/session')
//                ->setLastViewedStore($this->getRequest()->getParam('store'));
//            Mage::getSingleton('admin/session')
//                ->setLastEditedMenu($menu->getId());
            $this->_initLayoutMessages('adminhtml/session');
            $this->loadLayout();

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'content' => $this->getLayout()->getBlock('menu.edit')->getFormHtml()
                    . $this->getLayout()->getBlock('menu.tree')
                        ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingMenuBreadcrumbs')
                ,
                'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(),
            )));

            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('templates_master/navigationpro');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
            ->setContainerCssClass('navigationpro-menu');

        $this->_addBreadcrumb(Mage::helper('navigationpro')->__('Manage Menu'),
             Mage::helper('navigationpro')->__('Manage Menu')
        );

        $this->renderLayout();
    }

    public function saveAction()
    {
        $request = $this->getRequest();
        $data    = $request->getPost();

        if (!empty($data['general']['category_id'])
            && !empty($data['general']['root_menu_id'])) {

            // new menu for category
            $menu = $this->_initMenu(
                $data['general']['root_menu_id'],
                $data['general']['category_id']
            );
        } else {
            $menuId = 0;
            if (!empty($data['general']['menu_id'])) {
                $menuId = $data['general']['menu_id'];
            }
            unset($data['general']['menu_id']);
            $menu = $this->_initMenu($menuId);
        }

        $menu->addData($data['general'])
            ->setContent($request->getPost('content', array()))
            ->setColumns($request->getPost('columns', array()))
            ->setSiblings($request->getPost('siblings', array()))
            ->save();

        $refreshTree = 'true';
        $url = $this->getUrl('*/*/edit', array('_current' => true, 'menu_id' => $menu->getId()));

        $this->getResponse()->setBody(
            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, '.$refreshTree.');</script>'
        );
    }

    public function deleteAction()
    {
        if ($id = (int) $this->getRequest()->getParam('menu_id')) {
            try {
                $menu = Mage::getModel('navigationpro/menu')->load($id);
                $menu->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('navigationpro')->__('The menu has been deleted.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('navigationpro')->__('An error occurred while trying to delete the menu.'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('_current'=>true, 'menu_id'=>null)));
    }

    /**
     * Overriden Mage_Adminhtml_Catalog_CategoryController::wysiwygAction
     * Changed block type to allow to use widgets
     */
    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock(
            'tmcore/adminhtml_widget_form_element_wysiwyg_content',
            '',
            array(
                'editor_element_id' => $elementId,
                'store_id'          => $storeId,
                'store_media_url'   => $storeMediaUrl,
            )
        );

        $this->getResponse()->setBody($content->toHtml());
    }

    /**
     * Tree Action
     * Retrieve category tree
     *
     * @return void
     */
    public function treeAction()
    {
        // $storeId    = (int) $this->getRequest()->getParam('store');
        $categoryId = $this->getRequest()->getParam('category_id');

        if ($categoryId) {
            $menuId = $this->getRequest()->getParam('root_menu_id');
        } else {
            $menuId = $this->getRequest()->getParam('menu_id');
        }

        $menu = $this->_initMenu($menuId, $categoryId);
        $path = '0/1/menu_' . ($menu->isRoot() ? $menu->getId() : $menu->getRootMenuId());
        if ($category = Mage::registry('category')) {
            $pathIds = $category->getPathIds();
            array_shift($pathIds);
            $path .= '/' . implode('/', $pathIds);
        }

        $block = $this->getLayout()->createBlock('navigationpro/adminhtml_catalog_category_tree');
        $root  = $block->getAbsoluteRoot();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
            'data' => $block->getTree(),
            'parameters' => array(
                'text'         => $block->buildNodeName($root),
                'draggable'    => false,
                'allowDrop'    => false,
                'id'           => (int) $root->getId(),
                'expanded'     => (int) $block->getIsWasExpanded(),
                'store_id'     => (int) $block->getStore()->getId(),
                'root_menu_id' => $menu->getRootMenuId(),
                'category_id'  => $menu->getCategoryId(),
                'menu_id'      => $menu->isRoot() ? 'menu_' . $menu->getId() : null,
                'path'         => $path
                // 'category_id' => (int) $category->getId(),
                // 'root_visible'=> true//(int) $root->getIsVisible()
            )
        )));
    }

    /**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(false);
        }

        $categoryId = $this->getRequest()->getParam('category_id');
        $menuId     = $this->getRequest()->getParam('root_menu_id');

        $menu = $this->_initMenu($menuId, $categoryId);
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('navigationpro/adminhtml_catalog_category_tree')
                ->getTreeJson(Mage::registry('category'))
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/navigationpro');
    }
}
