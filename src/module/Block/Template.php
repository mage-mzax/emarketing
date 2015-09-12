<?php
/**
 * Mzax Emarketing (www.mzax.de)
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this Extension in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @version     {{version}}
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */



/**
 * Template block helper class with usefull methods
 * 
 *
 * @method Mage_Sales_Model_Order getOrder()
 * @method Mage_Sales_Model_Quote getQuote()
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Block_Template extends Mage_Core_Block_Template
{
    
    
    
    /**
     * Format Price
     * 
     * @param number $price
     * @param string $includeContainer
     * @return string
     */
    public function formatPrice($price, $includeContainer = false)
    {
        return Mage::app()->getStore()->formatPrice($price, $includeContainer);
    }
    



    /**
     * Retreive cross-sell product list
     *
     * @param mixed $object
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function getCrosssellProducts($object)
    {
        $productIds = $this->extractProductIds($object);
        $collection = $this->getLinkedProducts($productIds,
                Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL);
    
        return $collection;
    }
    
    
    

    /**
     * Retreive up-sell product list
     *
     * @param mixed $object
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function getUpsellProducts($object)
    {
        $productIds = $this->extractProductIds($object);
        $collection = $this->getLinkedProducts($productIds, 
                          Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL);
        
        return $collection;
    }
    
    
    /**
     * Retreive related product list
     * 
     * @param mixed $object
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function getRelatedProducts($object)
    {
        $productIds = $this->extractProductIds($object);
        $collection = $this->getLinkedProducts($productIds, 
                          Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED);
        
        return $collection;
    }
    
    
    
    
    /**
     * Extract all product ids from a given object,
     * this can be an order, quote or product
     * 
     * @param mixed $object
     * @return array
     */
    public function extractProductIds($object)
    {
        $productIds = array();
        
        if($object instanceof Mage_Sales_Model_Order ||
                $object instanceof Mage_Sales_Model_Quote)
        {
            foreach($object->getAllVisibleItems() as $item) {
                $productIds[] = $item->getProductId();
            }
        }
        
        if($object instanceof Mage_Catalog_Model_Product) {
            $productIds[] = $object->getId();
        }
        
        return $productIds;
    }
    
    
    
    
    /**
     * Retrieve linked products
     * 
     * @param array $productIds
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function getLinkedProducts($productIds, $linkType = Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED)
    {
        /* @var $linkModel Mage_Catalog_Model_Product_Link */
        $linkModel = Mage::getModel('catalog/product_link');
        $linkModel->setLinkTypeId($linkType);
        
        $productIds = (array) $productIds;
        
        /* @var $collection Mage_Catalog_Model_Resource_Product_Link_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_link_product_collection');
        $collection->setLinkModel($linkModel)
                   ->addProductFilter($productIds)
                   ->addExcludeProductFilter($productIds);
        
        return $collection;
    }
    
    
    
    
    /**
     * Retrieve all items from order or quote and
     * include product object
     * 
     * @param mixed $object
     * @param string $attributes
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getAllItems($object, $attributes = '*', $limit = 20)
    {
        $result = array();
        
        /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
        $collection = $object->getItemsCollection();
        
        $productsCollection = Mage::getResourceModel('catalog/product_collection')
            ->addIdFilter($collection->getColumnValues('product_id'))
            //->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds())
            ->addAttributeToSelect($attributes)
            ->addPriceData()
            ->setPageSize($limit)
            ->load();
        
        foreach($collection as $item) {
            $product = $productsCollection->getItemById($item->getProductId());
            if($product) {
                $item->setProduct($product);
                if(!$item->getParentItem()) {
                    $result[] = $item;
                }
            }
        }
        return $result;
    }
    
    
    
    /**
     * Retrieve product
     * 
     * @param mixed $object
     * @return Mage_Catalog_Model_Product|NULL
     */
    public function getProduct($object = null)
    {
        if(!$object) {
            return $this->getData('product');
        }
        if($object instanceof Mage_Catalog_Model_Product) {
            return $object;
        }
        if( $object instanceof Mage_Sales_Model_Order_Item || 
            $object instanceof Mage_Sales_Model_Quote_Item || 
            $object instanceof Mage_Sales_Model_Order_Invoice_Item ||
            $object instanceof Mage_Sales_Model_Order_Shipment_Item ||
            $object instanceof Mage_Sales_Model_Order_Creditmemo_Item) 
        {
            return $object->getProduct();
        }
        
        // @todo check for productId or sku?
        return null;
    }
    
    
    
    
    /**
     * Retrieve catalog image helper instance
     * 
     * @param mixed $object
     * @param string $attribute
     * @param number $size
     * @return Mage_Catalog_Helper_Image
     */
    public function getProductImage($object, $attribute = 'small_image', $size = 100)
    {
        $product = $this->getProduct($object);
        $helper  = $this->helper('catalog/image');
        if($product) {
            $helper->init($product, 'small_image')
                   ->constrainOnly(true)
                   ->keepAspectRatio(true)
                   ->keepFrame(false)
                   ->setQuality(40)
                   ->resize($size, $size);
        }
        return $helper;
    }
    
    
    
}