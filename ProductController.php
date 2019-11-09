<?php

namespace App\Presenters;

use Caching;
use IElasticSearchDriver;

class ProductPresenter extends BasePresenter
{
    /** @var Caching/Cache @inject */
    private $cache;
    
    /** @var App/Model/Product @inject */
    private $product;
    
    /** @var App/components/ElasticSearchDriver @inject */
    private $elasticSearch;
    
    /*public function __construct(\Nette\Caching\Cache $cache, \model\Product $product, ElasticSearchDriver $elasticSearch) 
    {
        $this->cache = $cache;
        $this->product = $product;
        $this->elasticSearch = $elasticSearch;
    }*/
    
    /**
     * get product detail
     * @param int $id
     * @return string
     */
    public function detail($id)
    {
        if(empty($id))
        {
            throw new Exception('Error 404');
        }
        
        $data = $this->loadProduct($id);
        return json_encode($data);
    }
    
    /**
     * load product 
     * @param int $id
     * @return type
     */    
    private function loadProduct($id)
    {
        $response = $this->cache->load($id);
        
        if($response === NULL)
        {
            return $this->loadFromElasticSearch($id);
        }
        else
        {            
            return $response;
        }
    }
     
    /**
     * load product from elasticSearch
     * @param int $id
     * @return type
     */
    private function loadFromElasticSearch($id)
    {
        $response = $this->elasticSearch->findById($id);
        
        if($response === NULL)
        {
            $this->loadFromDatabase($id);
        }
        else 
        {
            return $response;
        }
    }

    /**
     * load product from database
     * @param int $id
     * @return type
     * @throws Exception
     */
    private function loadFromDatabase($id)
    {
        $response = $this->product->getProduct($id);
        if($response === NULL)
        {
            throw new Exception('Error 404');
        }
        else
        {
            //save sache
            $this->cache->save($id, $response);          
            return $response;
        }
    }
    
}
