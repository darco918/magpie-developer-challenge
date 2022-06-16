<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;
use \Datetime;

class Product
{
    public static function extractProduct(Crawler $node)
    {
        $product = array();
        
        $name = $node -> filter('h3 > span'); //get the name of the product
        $capacity = $name -> eq(1) -> text();
        
        $product['title'] = ($name -> eq(0) -> text()) . ' ' . $capacity; //the name + the capacity
        $product['capacityMb'] = (int)$capacity * 1000; //capacity of the product
        
        $price = $node -> filter('div.my-8.block.text-center.text-lg') -> text(); //get the price
        $price = str_replace('£', '', $price); //remove £ symbol
        $product['price'] = $price;
        
        $imageUrl = $node -> filter('.my-8.mx-auto') -> image() ->getUri(); //get img
        $product['imageUrl'] = $imageUrl;

        $isAvailable  = true; //will be checked in the if statement below
        $availabilityText = ($node -> filter('div.my-4.text-sm.block.text-center')) -> eq(0) -> text(); //get the text
        $availabilityText = str_replace('Availability: ', '', $availabilityText); //remove the word Availability

        if($availabilityText == "Out of Stock") //check if it is available
            $isAvailable = false;
        else 
            $isAvailable = true;
        
        $product['availabilityText'] = $availabilityText;
        $product['isAvailable'] = $isAvailable;

        
        //Some products may have additional text, with a date that will need to be extracted
        $belowText = $node -> filter('div.my-4.text-sm.block.text-center');
        if(count($belowText) > 1){
            $product['shippingText'] = $belowText -> eq(1) -> text();
            
            $shippingDate = Product:: extractDate($belowText -> eq(1) -> text());
            if($shippingDate)
                $product['shippingDate'] = $shippingDate;
        }

        return $product;
        
    }
    
    private static function extractDate(string $shippingText)
    {
              $months = ["Jan" => "01", "Feb" => "02", "Mar" => "03", "Apr" => "04", "May" => "05", "Jun" => "06",
                        "Jul" => "07", "Aug" => "08", "Sep" => "09", "Oct" => "10", "Nov" => "11", "Dec" => "12"];

              if(str_contains($shippingText, 'tomorrow')) //check if the text contains the word tomorrow, like the Huaweii phone
                    return (new DateTime('tomorrow')) -> format('Y-m-d');
              else{
                    $regx = '/\d{4}-\d{1,2}-\d{1,2}/'; //check if the text contains a date
                    preg_match($regx, $shippingText, $shippingDate);
                    
                    if(!empty($shippingDate))
                        return $shippingDate[0];
                    else{
                        //check for the name of the month
                        $regx= '/\d{1,2}([a-z]{2})?\s[A-Z][a-z]{2}\s\d{4}/';
                        preg_match($regx, $shippingText, $shippingDate);

                        if(!empty($shippingDate)){
                            $date = explode(" ", $shippingDate[0]); //split shipping date in 3, using the space character
                            $day = $date[0];
                            
                            $day = preg_replace('/[a-z]{2}/',"", $day);
                            if($day < 10 and $day > 0)
                                $day = '0' . $day;
                            
                            //change month to numbers
                            $month = $months[$date[1]];
                            $finalDate = $date[2] . "-" . $month . "-" . $day;
                            return $finalDate;
                        }
                  }
            }
    }
}
