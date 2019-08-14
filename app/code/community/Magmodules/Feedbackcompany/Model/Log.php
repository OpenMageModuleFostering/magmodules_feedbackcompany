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
 
class Magmodules_Feedbackcompany_Model_Log extends Mage_Core_Model_Abstract {

	public function _construct() 
	{
		parent::_construct();
		$this->_init('feedbackcompany/log');
	}

	public function addToLog($type, $storeId, $review = '', $response = '', $time, $crontype = '', $api_url = '', $orderId = '') 
	{
		if(Mage::getStoreConfig('feedbackcompany/log/enabled')) {
			
			if($type == 'productreview') {
				$api_id	= Mage::getStoreConfig('feedbackcompany/productreviews/client_token', $storeId);
				$api_url = Mage::getStoreConfig('feedbackcompany/productreviews/client_token', $storeId);
			} else {
				$api_id	= Mage::getStoreConfig('feedbackcompany/general/api_id', $storeId);		
			}
			
			$company = Mage::getStoreConfig('feedbackcompany/general/company', $storeId);
			$review_updates	= '';
			$review_new	= '';

			if($review) {
				if(!empty($review['review_updates'])) {
					$review_updates	= $review['review_updates'];
				}	
				if(!empty($review['review_new'])) {
					$review_new	= $review['review_new'];
				}
				if(!empty($review['stats']['msg'])) {
					$response = $review['stats']['msg'];
				}				
			}

			$model = Mage::getModel('feedbackcompany/log');
			$model->setType($type)
				->setShopId($api_id)
				->setStoreId($storeId)
				->setCompany($company)
				->setReviewUpdate($review_updates)
				->setReviewNew($review_new)
				->setResponse($response)
				->setOrderId($orderId)
				->setCron($crontype)
				->setDate(now())
				->setTime($time)
				->setApiUrl($api_url)
				->save();
		}
	}

}