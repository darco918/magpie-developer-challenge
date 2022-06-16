<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

require 'vendor/autoload.php';

class Scrape{
    private array $products = [];
	
    public function run(): void
    {
		$numPages = 3;
    	for($i = 1; $i <= $numPages; $i++){
		    $document = ScrapeHelper::fetchDocument('https://www.magpiehq.com/developer-challenge/smartphones/?page='. $i);
		    $document = $document -> filter("div.bg-white.p-4.rounded-md"); 

		    foreach($document as $el){
			    $node = new Crawler($el -> ownerDocument -> saveHTML($el),
				'https://www.magpiehq.com/developer-challenge/smartphones/?page='. $i);
			    
			    
			    $newProduct = Product::extractProduct($node);
			    

				//Get the colors here because some products may have 2+ colors that need to be 
				//treated like different products.
			    $colors = $node -> filter('div.px-2 > span') -> extract(['data-colour']);
				
			    foreach($colors as $color)
			    {
					    $newProduct['color'] = $color; //add the color

					    if(!$this -> isDuplicated($newProduct,$this -> products))
						    $this -> products[] = $newProduct;
			    }
		    }
		}
	    file_put_contents('output.json', json_encode($this->products, JSON_PRETTY_PRINT ));
    }
    
    private function isDuplicated(array $newProduct, array $existingProducts)
    {
    	foreach($existingProducts as $product)
    		if($newProduct['title'] == $product['title'] and $newProduct['capacityMb'] == $product['capacityMb'] 
				and ($newProduct['color'] == $product['color']))
    				return true;			
    	return false;
    }
}

$scrape = new Scrape();
$scrape->run();
