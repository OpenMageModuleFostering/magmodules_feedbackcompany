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

class Magmodules_Feedbackcompany_Model_Reviews extends Mage_Core_Model_Abstract {

    const CACHE_TAG  = 'feedback_block';

	public function _construct() 
	{
		parent::_construct();
		$this->_init('feedbackcompany/reviews');
	}

	public function processFeed($feed, $storeid = 0, $type) 
	{ 
		$updates = 0; $new = 0; $history = 0;
		$api_id	= Mage::getStoreConfig('feedbackcompany/general/api_id', $storeid);
		$company = Mage::getStoreConfig('feedbackcompany/general/company', $storeid);	

		foreach($feed->reviewDetails->reviewDetail as $review) {
			
			$feedback_id = $review->id;
			$score = ($review->score / 2);
			$score_max = ($review->scoremax / 2);
			$review_text = $review->text;
			$score_aftersales = ($review->score_aftersales / 2);
			$score_checkout = ($review->score_bestelgemak / 2);
			$score_information = ($review->score_informatievoorziening / 2);
			$score_friendly = ($review->score_klantvriendelijk / 2);
			$score_leadtime = ($review->score_levertijd / 2);
			$score_responsetime = ($review->score_reactiesnelheid / 2);
			$score_order = ($review->score_orderverloop / 2);
			$customer_name = $review->user;
			$customer_recommend = $review->beveeltAan;
			$customer_active = $review->kooptvakeronline;
			$customer_sex = $review->geslacht;
			$customer_age = $review->leeftijd;
			$purchased_products = $review->gekochtproduct;
			$text_positive = $review->sterkepunten;
			$text_improvements = $review->verbeterpunten;
			$company_response = $review->companyResponse;
			$date_created = $review->createdate;
			$date_created = substr($date_created, 0, 4) . '/' . substr($date_created, 4, 2) . '/' . substr($date_created, 6, 2);						
			$indatabase = $this->getCollection()->addFieldToFilter('feedback_id', $feedback_id)->getFirstItem();
			
			if($indatabase->getReviewId()) {
				if(($type == 'history') || ($type == 'all')) {
					$reviews = Mage::getModel('feedbackcompany/reviews');
					$reviews->setReviewId($indatabase->getReviewId())
							->setShopId($api_id)
							->setCompany($company)
							->setFeedbackId($feedback_id)
							->setReviewText($review_text)
							->setScore($score)
							->setScoreMax($score_max)
							->setScoreAftersales($score_aftersales)
							->setScoreCheckout($score_checkout)
							->setScoreInformation($score_information)
							->setScoreFriendly($score_friendly)
							->setScoreLeadtime($score_leadtime)
							->setScoreResponsetime($score_responsetime)
							->setScoreOrder($score_order)
							->setCustomerName($customer_name)
							->setCustomerRecommend($customer_recommend)
							->setCustomerActive($customer_active)
							->setCustomerSex($customer_sex)
							->setCustomerAge($customer_age)
							->setPurchasedProducts($purchased_products)
							->setTextPositive($text_positive)
							->setTextImprovements($text_improvements)
							->setCompanyResponse($company_response)							
							->setDateCreated($date_created)
							->save();
					$updates++;
				} 
			} else {
				$reviews = Mage::getModel('feedbackcompany/reviews');
				$reviews->setShopId($api_id)
						->setCompany($company)
						->setFeedbackId($feedback_id)
						->setReviewText($review_text)
						->setScore($score)
						->setScoreMax($score_max)
						->setScoreAftersales($score_aftersales)
						->setScoreCheckout($score_checkout)
						->setScoreInformation($score_information)
						->setScoreFriendly($score_friendly)
						->setScoreLeadtime($score_leadtime)
						->setScoreResponsetime($score_responsetime)
						->setScoreOrder($score_order)
						->setCustomerName($customer_name)
						->setCustomerRecommend($customer_recommend)
						->setCustomerActive($customer_active)
						->setCustomerSex($customer_sex)
						->setCustomerAge($customer_age)
						->setPurchasedProducts($purchased_products)
						->setTextPositive($text_positive)
						->setTextImprovements($text_improvements)
						->setCompanyResponse($company_response)						
						->setDateCreated($date_created)
						->save();
				$new++;
			}
		}

		$config = new Mage_Core_Model_Config();
		$config->saveConfig('feedbackcompany/reviews/lastrun', now(), 'default', 0);
		$result = array();
		$result['review_updates'] = $updates; 
		$result['review_new'] = $new; 
		$result['company'] = $company;
		return $result; 
	}
			
	public function flushCache() 
	{
		if(Mage::getStoreConfig('feedbackcompany/reviews/flushcache')) {
			Mage::app()->cleanCache(array(Mage_Cms_Model_Block::CACHE_TAG, Magmodules_Feedbackcompany_Model_Reviews::CACHE_TAG));
		}	
	}

}