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

class Magmodules_Feedbackcompany_Model_Productreviews extends Mage_Core_Model_Abstract {

	public function processFeed($feed, $storeid = 0) 
	{ 
		$new = 0; $updates = 0;
		$feed = $feed['feed'];
		$status_id = Mage::getStoreConfig('feedbackcompany/productreviews/review_import_status', $storeid);
		$rating_id = Mage::getStoreConfig('feedbackcompany/productreviews/review_import_rating', $storeid);			
		$options = $this->getRatingOptionArray($rating_id);
		foreach($feed->product_reviews as $review) {									
			$feedback_id = $review->product_opinion_id;
			$_loadrev = Mage::getModel('review/review')->load($feedback_id, 'feedbackcompany_id');
			if(($_loadrev->getReviewId() < 1) && ($review->rating > 0)) {
				$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $review->product_sku);								
				if($_product) {
					$content = $review->review;					
					if((strlen($content) > 0) && ($review->rating > 0) && (!empty($options[$review->rating]))) {
						try {												
							$title = $this->getFirstSentence($content);
							if(strlen($title) > 0) {
								$date_created = Mage::getModel('core/date')->timestamp($review->date_created); 
								$created_at = date('Y-m-d H:i:s', $date_created);
								$_review = Mage::getModel('review/review');
								$_review->setEntityPkValue($_product->getId());
								$_review->setCreatedAt($created_at);
								$_review->setTitle($title);
								$_review->setFeedbackcompanyId($feedback_id);		
								$_review->setDetail($content);
								$_review->setEntityId(1);                                      
								$_review->setStoreId(0);          
								$_review->setStatusId($status_id); 
								$_review->setCustomerId(null);
								$_review->setNickname($review->client->name);
								$_review->setStores($this->getAllStoreViews($storeid));
								$_review->setSkipCreatedAtSet(true);
								$_review->save();
								$_rating = Mage::getModel('rating/rating');
								$_rating->setRatingId($rating_id);        
								$_rating->setReviewId($_review->getId());         
								$_rating->setCustomerId(null);         
								$_rating->addOptionVote($options[$review->rating], $_product->getId());  
								$_review->aggregate();
								$new++;
							}
						} catch (Exception $e) {
							Mage::log($e->getMessage(), null, 'feedbackcompany.log');
							//Mage::getSingleton('adminhtml/session')->addError(Mage::helper('feedbackcompany')->__('There has been an error, please check var/log/feedbackcompany.log'));
						}
					}
				}					
			}
		}			
		$config = new Mage_Core_Model_Config();
		$config->saveConfig('feedbackcompany/productreviews/lastrun', now(), 'default', 0);
		$result = array();
		$result['review_updates'] = $updates; 
		$result['review_new'] = $new; 
		return $result;
	}

	public function getFirstSentence($string)
	{
		$string = str_replace(" .",".",$string);
		$string = str_replace(" ?","?",$string);
		$string = str_replace(" !","!",$string);
		preg_match('/^.*[^\s](\.|\?|\!)/U', $string, $match);
		if(!empty($match[0])) {
			return $match[0]; 
		} else {
			return Mage::helper('core/string')->truncate($string, 50) . '...';		
		}	
	} 

	public function getAllStoreViews($storeid) 
	{
		$client_id = Mage::getStoreConfig('feedbackcompany/productreviews/client_id', $storeid);			
		$client_secret = Mage::getStoreConfig('feedbackcompany/productreviews/client_secret', $storeid);			
		$reviewstores = array();
		$stores = Mage::getModel('core/store')->getCollection();
		foreach ($stores as $store) {		
			if($store->getIsActive()) {
				if(Mage::getStoreConfig('feedbackcompany/productreviews/enabled', $store->getId())) {
					$st_client_id = Mage::getStoreConfig('feedbackcompany/productreviews/client_id', $store->getId());			
					$st_client_secret = Mage::getStoreConfig('feedbackcompany/productreviews/client_secret', $store->getId());			
					if(($client_id == $st_client_id) && ($client_secret == $st_client_secret)) {
						$reviewstores[] = $store->getId();
					}
				}	
			}
		}
		return $reviewstores;
	}

	public function getRatingOptionArray($rating_id) 
	{
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = "SELECT * FROM " .  $resource->getTableName('rating/rating_option') . " WHERE rating_id = '" . $rating_id . "'";
		$results = $readConnection->fetchAll($query);
		$options = array();
		foreach ($results as $option) {
			$options[$option['value']] = $option['option_id'];			
		}
		return $options;
	}

}