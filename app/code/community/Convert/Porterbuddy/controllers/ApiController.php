<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_ApiController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Mage_Catalog_Helper_Image
     */
    protected $imageHelper;

    /**
     * @var Mage_Catalog_Model_Product_Url
     */
    protected $urlModel;

    protected function _construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
        $this->imageHelper = Mage::helper('catalog/image');
        $this->urlModel = Mage::getSingleton('catalog/factory')->getProductUrlInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->setFlag('', self::FLAG_NO_START_SESSION, true);

        parent::preDispatch();

        $token = $this->getRequest()->getHeader('token');
        $expectedToken = $this->helper->getInboundToken();
        $tokenValid = strlen($token) && $this->helper->compareString($expectedToken, $token);
        if (!$tokenValid) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);

            return $this->prepareDataJSON(array(
                'error' => true,
                'message' => 'Invalid token.'
            ));
        }

        return $this;
    }

    /**
     * @return Zend_Controller_Response_Abstract
     */
    public function productsAction()
    {
        $storeId = $this->getRequest()->getQuery('store') ?: Mage::app()->getDefaultStoreView()->getId();
        $limit = (int)$this->getRequest()->getQuery('limit', 500);
        $page = (int)$this->getRequest()->getQuery('page', 1);

        try {
            Mage::app()->getStore($storeId)->getId();
        } catch (Mage_Core_Model_Store_Exception $e) {
            return $this->prepareDataJSON(array(
                'error' => true,
                'message' => 'Store does not exist',
            ));
        }

        //Varien_Profiler::enable();
        Varien_Profiler::start('products::action');

        Varien_Profiler::start('products::load_products');
        $productCollection = $this->getProductCollection($storeId, $limit, $page);
        $productCollection->load();
        Varien_Profiler::stop('products::load_products');

        Varien_Profiler::start('products::add_url_rewrite');
        $productCollection->addUrlRewrite();
        Varien_Profiler::stop('products::add_url_rewrite');

        // add category ids to loaded items
        Varien_Profiler::start('products::add_category_ids');
        $productCollection->addCategoryIds();
        Varien_Profiler::stop('products::add_category_ids');

        // build category tree
        Varien_Profiler::start('products::menu_tree');
        $categoryNodesIndex = $this->buildCategoryTree();
        Varien_Profiler::stop('products::menu_tree');

        Varien_Profiler::start('products::build_result');
        $this->outputResult($productCollection, $categoryNodesIndex);
        Varien_Profiler::stop('products::build_result');

        Varien_Profiler::stop('products::action');
    }

    /**
     * @param int $storeId
     * @param int $limit
     * @param int $page
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function getProductCollection($storeId, $limit, $page)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->setStoreId($storeId)
            ->addAttributeToSelect(array('sku', 'name', 'description'))
            ->addPriceData(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        if ($limit) {
            $collection->setPageSize($limit);
        }
        if ($page) {
            $collection->setCurPage($page);
        }

        Mage::dispatchEvent('convert_porterbuddy_api_products_collection', array(
            'collection' => $collection,
        ));

        return $collection;
    }

    /**
     * @return array
     */
    protected function buildCategoryTree()
    {
        $menu = new Varien_Data_Tree_Node(array(), 'root', new Varien_Data_Tree());

        $categoryNodesIndex = array();
        $storeCategories = Mage::helper('catalog/category')->getStoreCategories();
        $this->addCategoriesToMenu(
            $storeCategories,
            $menu,
            $categoryNodesIndex
        );
        return $categoryNodesIndex;
    }

    /**
     * @param Mage_Catalog_Model_Category[] $categories
     * @param Varien_Data_Tree_Node $parentCategoryNode
     * @param array $categoryNodesIndex
     * @see Mage_Catalog_Model_Observer::_addCategoriesToMenu
     */
    protected function addCategoriesToMenu($categories, $parentCategoryNode, array &$categoryNodesIndex)
    {
        /** @var Mage_Catalog_Model_Category $categoryModel */
        $categoryModel = Mage::getModel('catalog/category');
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $nodeId = 'category-node-' . $category->getId();

            $categoryModel->setId($category->getId());

            $tree = $parentCategoryNode->getTree();
            $categoryData = array(
                'name' => $category->getName(),
                'id' => $nodeId,
            );
            $categoryNode = new Varien_Data_Tree_Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            $categoryNodesIndex[$category->getId()] = $categoryNode;

            $flatHelper = Mage::helper('catalog/category_flat');
            if ($flatHelper->isEnabled() && $flatHelper->isBuilt(true)) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->addCategoriesToMenu($subcategories, $categoryNode, $categoryNodesIndex);
        }
    }

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param array $categoryNodesIndex
     */
    protected function outputResult($collection, $categoryNodesIndex)
    {
        Mage::dispatchEvent('convert_porterbuddy_api_products_output_before', array(
            'collection' => $collection,
            'category_nodes_index' => $categoryNodesIndex,
        ));

        $this->getResponse()
            ->clearBody();
        $this->getResponse()
            ->setHeader('Content-type', 'application/json', true)
            ->sendHeaders();

        $summary = array(
            'store' => $collection->getStoreId(),
            'total' => $collection->getSize(),
            'loaded' => count($collection),
            'limit' => $collection->getPageSize(),
            'page' => $collection->getCurPage(),
        );
        $transport = new Varien_Object(array('summary' => $summary));
        Mage::dispatchEvent('convert_porterbuddy_api_products_summary', array(
            'collection' => $collection,
            'category_nodes_index' => $categoryNodesIndex,
            'transport' => $transport,
        ));
        $summary = $transport->getData('summary');

        // output rows one by one to save memory
        print '{"summary":' . json_encode($summary) . ',"products":[';

        $first = true;
        /** @var Mage_Catalog_Model_Product $product */
        foreach ($collection as $product) {
            $row = $this->formatProductRow($product, $categoryNodesIndex);
            if ($first) {
                $first = false;
            } else {
                print ',';
            }
            print json_encode($row);
            flush();
        }
        print ']}';
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param $categoryNodesIndex
     * @return array
     */
    protected function formatProductRow(Mage_Catalog_Model_Product $product, $categoryNodesIndex)
    {
        $categoryIds = $product->getCategoryIds();
        $categoryNames = array();
        foreach ($categoryIds as $categoryId) {
            $names = $this->getCategoryNames($categoryId, $categoryNodesIndex);
            if ($names) {
                $categoryNames[] = $names;
            }
        }

        $row = array(
            'id' => $product->getId(),
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getFinalPrice(),
            'link' => $this->urlModel->getUrl($product, array('_ignore_category' => true)),
            'image' => (string)$this->imageHelper->init($product, 'image'),
            'categories' => $categoryNames,
        );

        $transport = new Varien_Object(array('row' => $row));
        Mage::dispatchEvent('convert_porterbuddy_api_products_format_row', array(
            'product' => $product,
            'category_nodes_index' => $categoryNodesIndex,
            'transport' => $transport,
        ));
        $row = $transport->getData('row');

        return $row;
    }

    /**
     * @param int $categoryId
     * @param array $categoryNodesIndex
     * @return array
     */
    protected function getCategoryNames($categoryId, array $categoryNodesIndex)
    {
        $names = array();
        if (!isset($categoryNodesIndex[$categoryId])) {
            return $names;
        }

        $categoryNode = $categoryNodesIndex[$categoryId];
        do {
            $names[] = $categoryNode->getName();
        } while (($categoryNode = $categoryNode->getParent()) && $categoryNode->getId());

        return array_reverse($names);
    }

    /**
     * Prepare JSON formatted data for response to client
     *
     * @param $response
     * @return Zend_Controller_Response_Abstract
     */
    protected function prepareDataJSON($response)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
