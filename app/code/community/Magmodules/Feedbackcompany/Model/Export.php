<?php 
/**
 * Magmodules.eu - http://www.magmodules.eu
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category	Magmodules
 * @package		Magmodules_Feedbackcompany
 * @author		Magmodules <info@magmodules.eu)
 * @copyright	Copyright (c) 2016 (http://www.magmodules.eu)
 * @license		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Magmodules_Feedbackcompany_Model_Export extends Mage_Core_Model_Abstract {

	public function getFeed($reviews, $store_id) 
	{
		$csv_data = array();
		$csv_data[] = $this->getHeader();
		foreach($reviews as $reviewId) {
			$review = Mage::getModel('review/review')->load($reviewId);
			$product = Mage::getModel('catalog/product')->setStoreId($store_id)->load($review->getEntityPkValue());
			if($review && $product) {			
				$text = trim($review->getTitle()) . ' ' . trim(preg_replace('/\s+/', ' ', $review->getDetail()));
				if(strlen($text) > 1) { 
					$date = date('Ymd', strtotime($review->getCreatedAt()));
					$name = Mage::helper('core/string')->truncate($review->getNickname(), 250);
					$email = '';
					$gender = '';
					$city = '';
					$country = '';			
					$product_name = trim($product->getName());
					$product_review = str_replace(';', '', $text);
					$product_id = $review->getEntityPkValue();
					$product_sku = $product->getSku();			
					$product_url = $product->getProductUrl();
					if($product_url) {
						$product_url = preg_replace('/\?.*/', '', $product_url);
					}	
					$product_opinion_id = $review->getFeedbackcompanyId();
					$score = array();
					$votes = Mage::getModel('rating/rating_option_vote')->getResourceCollection()->setReviewFilter($reviewId)->setStoreFilter($store_id)->load();
					foreach($votes as $vote) {
						if($vote->getPercent() > 0) {
							$score[] = $vote->getPercent();
						}	
					}
					if(count($votes) > 0) {
						$product_score = round(((array_sum($score) / count($votes)) / 20), 2);
					} else {
						$product_score = '';
					}		
					if($review->getCustomerId()) {
						$customer = Mage::getModel('customer/customer')->load($review->getCustomerId());
						$address = Mage::getModel('customer/address')->load($customer->getDefaultBilling());
						$email = $customer->getEmail();
						$city = $address->getCity();			
						$country = $address->getCountry();							
					}
					$csv_data[] = array($date, $name, $email, $gender, '', $city, $country, '', $product_name, $product_score, $product_review, $product_url, $product_id, $product_sku, $product_opinion_id);
				}
			}	
		}
		return $csv_data;
	}
	
	public function getHeader()  
	{
		$header = array(
            'date',
            'name', 
            'email',
            'gender',       
            'age',
            'city',
            'country',
            'vestiging',
            'product_name',
            'product_score',
            'product_review',
            'product_url',
            'product_id',
            'product_sku',            
			'product_opinion_id',
        );
        return $header;
	}
		
}