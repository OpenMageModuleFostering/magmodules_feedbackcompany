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
 
class Magmodules_Feedbackcompany_Model_Api extends Mage_Core_Model_Abstract {

	public function processFeed($storeid = 0, $type) 
	{
		if($feed = $this->getFeed($storeid, $type)) {
			$results = Mage::getModel('feedbackcompany/reviews')->processFeed($feed, $storeid, $type);
			$results['stats'] = Mage::getModel('feedbackcompany/stats')->processFeed($feed, $storeid);
			return $results;
		}
		return false;
	}

	public function getFeed($storeid, $type = '', $interval = '') 
	{
		if($type == 'productreviews') {			
			$result = array();
			$client_token = Mage::getStoreConfig('feedbackcompany/productreviews/client_token', $storeid);			
			if(!$client_token) {
				$client_token = $this->getOauthToken($storeid);
				if($client_token['status'] == 'ERROR') {
					return $client_token;
				} else {
					$client_token = $client_token['client_token'];
				}
			}	
			
			$request = curl_init();
			curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);		
			curl_setopt($request, CURLOPT_URL, 'https://beoordelingen.feedbackcompany.nl/api/v1/review/getrecent/?interval=' . $interval . '&type=product&unixts=1');
			curl_setopt($request, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $client_token));
			curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
			$api_result = json_decode($content = curl_exec($request));
			if($api_result) {			
				if(isset($api_result->message)) {
					if($api_result->message == 'OK') {
						$result['status'] = 'OK';
						$result['feed'] = $api_result->data[0];
						return $result;
					}
				}	
				$config = new Mage_Core_Model_Config();
				$config->saveConfig('feedbackcompany/productreviews/client_token', '', 'stores', $storeid);	
				Mage::app()->getCacheInstance()->cleanType('config');
				$result['status'] = 'ERROR';
				$result['error'] = $api_result->error;
				return $result;
			} else {
				$result['status'] = 'ERROR';
				$result['error'] = Mage::helper('feedbackcompany')->__('Error connect to the API.');
				return $result;		
			}
		} else {
			$api_id	= trim(Mage::getStoreConfig('feedbackcompany/general/api_id', $storeid));
			if($type == 'stats') {		
				$api_url = 'https://beoordelingen.feedbackcompany.nl/samenvoordeel/scripts/flexreview/getreviewxml.cfm?ws=' . $api_id . '&publishDetails=0&nor=0&Basescore=10';
			} 
			if(($type == 'reviews') || ($type == 'history')) {
				$api_url = 'https://beoordelingen.feedbackcompany.nl/samenvoordeel/scripts/flexreview/getreviewxml.cfm?ws=' . $api_id . '&publishIDs=1&nor=100&publishDetails=1&publishOnHold=0&sort=desc&emlpass=test&publishCompResponse=1&Basescore=10';
			}
			if($type == 'all') {
				$api_url = 'https://beoordelingen.feedbackcompany.nl/samenvoordeel/scripts/flexreview/getreviewxml.cfm?ws=' . $api_id . '&publishIDs=1&nor=10000&publishDetails=1&publishOnHold=0&sort=desc&emlpass=test&publishCompResponse=1&Basescore=10';
			}
						
			if($api_id) {
				$xml = simplexml_load_file($api_url);	
				if($xml) {
					return $xml;
				}
			}
		}	
		return false;
	}

	public function sendInvitation($order) 
	{
		$store_id 		= $order->getStoreId();
		$inv_status		= Mage::getStoreConfig('feedbackcompany/invitation/status', $store_id);
		$date_now 		= Mage::getModel('core/date')->timestamp(time());
		$date_order 	= Mage::getModel('core/date')->timestamp($order->getCreatedAt());
		$date_diff		= (($date_order - $date_now) / 86400);
		$backlog		= Mage::getStoreConfig('feedbackcompany/invitation/backlog', $store_id);
		$sent			= $order->getFeedbackSent();

		if($backlog < 1) { 
			$backlog = 30; 
		}		
		
		if(($order->getStatus() == $inv_status) && ($date_diff < $backlog) && (!$sent)) {
			
			$start_time 	= microtime(true);
			$crontype 		= 'orderupdate';
			$order_id 		= $order->getIncrementId(); 
			$api_id 		= Mage::getStoreConfig('feedbackcompany/general/api_id', $store_id);
			$api_key 		= Mage::getStoreConfig('feedbackcompany/invitation/connector', $store_id);
			$delay			= Mage::getStoreConfig('feedbackcompany/invitation/delay', $store_id);
			$resend			= Mage::getStoreConfig('feedbackcompany/invitation/resend', $store_id);
			$remind_delay	= Mage::getStoreConfig('feedbackcompany/invitation/remind_delay', $store_id);
			$min_order		= Mage::getStoreConfig('feedbackcompany/invitation/min_order_total', $store_id);
			$exclude_cat	= Mage::getStoreConfig('feedbackcompany/invitation/exclude_category', $store_id);
			$productreviews	= Mage::getStoreConfig('feedbackcompany/productreviews/enabled', $store_id);
			$email			= $order->getCustomerEmail();
			$order_number	= $order->getIncrementID();
			$order_total 	= $order->getGrandTotal();
			$aanhef			= $order->getCustomerName();
			$check_sum		= 0;
			$categories 	= array(); 
			$exclude_reason	= array(); 
			$request		= array(); 
			
			// SendInivation Request
			$request['action'] = 'sendInvitation';
						
			// Exclude by Category
			if($exclude_cat) {
				$excl_cats = Mage::getStoreConfig('feedbackcompany/invitation/exclude_categories', $store_id);
				$excl_categories = explode(',', $excl_cats);
			} else {
				$excl_categories = '';
			}
						
			// Get all Products
			$filtercode = array(); $i = 1;
			$website_url = Mage::app()->getStore($store_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
			$media_url = Mage::app()->getStore($store_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)  . 'catalog' . DS . 'product';

			foreach($order->getAllVisibleItems() as $item) {
				$filtercode[] = urlencode(trim($item->getSku()));
				$filtercode[] = urlencode(trim($item->getName()));
				if($productreviews) {
					$product = Mage::getModel('catalog/product')->setStoreId($store_id)->load($item->getProductId());
					if(($product->getStatus() == '1') && ($product->getVisibility() != '1')) {
						$var_url = urlencode('product_url[' .$i. ']');
						$var_text = urlencode('product_text[' .$i. ']');
						$var_id = urlencode('product_ids[' .$i. ']');
						$var_photo = urlencode('product_photo[' .$i. ']');
						if($product->getUrlPath()) {
							$deeplink = $website_url . $product->getUrlPath();			
							$image_url = '';						
							if($product->getImage() && ($product->getImage() != 'no_selection')) {
								$image_url = $media_url . $product->getImage(); 
							}
							$request[$var_url] = urlencode($deeplink); 
							$request[$var_text] = urlencode(trim($product->getName())); 
							$request[$var_id] = urlencode('SKU=' . trim($product->getSku())); 
							$request[$var_photo] = urlencode($image_url); 
							$i++;
						}	
					}
				}
				
				if($exclude_cat) {
					if(!$product) {
						$product = Mage::getModel('catalog/product')->setStoreId($store_id)->load($item->getProductId());				
					}
					$categories = array_merge($categories, $product->getCategoryIds());										
				}
			}
			
			$filtercode = implode(',', $filtercode);
		
			// Get Checksum
			for($i = 0; $i < strlen($email); $i++) { 
				$check_sum += ord($email[$i]); 
			}		

			$exclude = 0;	
			if(!empty($min_order)) {
				if($min_order >= $order_total) {
					$exclude = 1;
					$exclude_reason[] = Mage::helper('feedbackcompany')->__('Below minimum order value');
				}	
			}
						
			if($order->getStatus() != $inv_status) {
				$exclude = 1;
			}
			
			if($excl_categories) {
				foreach($categories as $cat) {
					if(in_array($cat, $excl_categories)) {
						$exclude = 1;
						$exclude_reason[] = Mage::helper('feedbackcompany')->__('Category is excluded');
					}
				}
			}	
			
			if($exclude == 1) {
				if($exclude_reason) {
					$reason = implode(',', array_unique($exclude_reason));
					$reason = 'Not invited: ' . $reason;
					$writelog = Mage::getModel('feedbackcompany/log')->addToLog('invitation', $order->getStoreId(), '', $reason, (microtime(true) - $start_time), $crontype, '', $order->getId());					
				} else {
					return false;
				}
			} else {

				$request['filtercode'] = $filtercode;
				$request['Chksum'] = $check_sum;
				$request['orderNumber'] = $order_number;
				$request['resendIfDouble'] = $resend;
				$request['remindDelay'] = $remind_delay;
				$request['delay'] = $delay;
				$request['aanhef'] = urlencode($aanhef);	
				$request['user'] = urlencode($email);
				$request['connector'] = $api_key;

				$post = '';
				foreach(array_reverse($request) as $key => $value) {
					$post .=  '&' . $key . '=' . trim($value); 
				}
				$post = trim($post, '&');

				// Connect to API
				$url = 'https://connect.feedbackcompany.nl/feedback/';
				$feedbackconnect = curl_init($url . '?' . $post);
				curl_setopt($feedbackconnect, CURLOPT_VERBOSE, 1);
				curl_setopt($feedbackconnect, CURLOPT_FAILONERROR, false);
				curl_setopt($feedbackconnect, CURLOPT_HEADER, 0);
				curl_setopt($feedbackconnect, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($feedbackconnect, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($feedbackconnect, CURLOPT_SSL_VERIFYPEER, false);		
				$response = curl_exec($feedbackconnect);
				curl_close($feedbackconnect);

				if($response) {
					if($response == 'Request OK.') {
						$order->setFeedbackSent(1)->save();
						$response_html = $response;					
					} else {
						$response_html = 'Error sending review request!';					
					}
				} else {
					$response_html = 'No response from https://connect.feedbackcompany.nl';
				}
	
				// Write to log
				$writelog = Mage::getModel('feedbackcompany/log')->addToLog('invitation', $order->getStoreId(), '', $response_html, (microtime(true) - $start_time), $crontype, $url . '?' . $post, $order->getId());
				return true;
			} 
		}
		return false;
	}

	public function getStoreIds($type = '')
	{
		$store_ids = array(); 
		$stores = Mage::getModel('core/store')->getCollection();
		if($type == 'oauth') {
			foreach ($stores as $store) {		
				if($store->getIsActive()) {
					$enabled = Mage::getStoreConfig('feedbackcompany/productreviews/enabled', $store->getId());
					$client_id = Mage::getStoreConfig('feedbackcompany/productreviews/client_id', $store->getId());
					if($enabled && $client_id) {						
						$store_ids[] = $store->getId();
					}
				}	
			}
			return $store_ids;
		} else {
			$api_ids = array();
			foreach ($stores as $store) {		
				if($store->getIsActive()) {
					$api_id	= Mage::getStoreConfig('feedbackcompany/general/api_id', $store->getId());
					if(!in_array($api_id, $api_ids)) {
						$api_ids[] = $api_id; $store_ids[] = $store->getId();
					}		
				}
			}
			return $store_ids;
		}
	}

	public function getOauthToken($storeid) 
	{
		$client_id = Mage::getStoreConfig('feedbackcompany/productreviews/client_id', $storeid);			
		$client_secret = Mage::getStoreConfig('feedbackcompany/productreviews/client_secret', $storeid);			
		$result = array();
		if(!empty($client_id) && !empty($client_secret)) {
			$url = "https://beoordelingen.feedbackcompany.nl/api/v1/oauth2/token";
			$get_array = array("client_id" => $client_id, "client_secret" => $client_secret, "grant_type" => "authorization_code");			  
			$feedbackconnect = curl_init($url . '?' . http_build_query($get_array));
			curl_setopt($feedbackconnect, CURLOPT_VERBOSE, 1);
			curl_setopt($feedbackconnect, CURLOPT_FAILONERROR, false);
			curl_setopt($feedbackconnect, CURLOPT_HEADER, 0);
			curl_setopt($feedbackconnect, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($feedbackconnect, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($feedbackconnect, CURLOPT_SSL_VERIFYPEER, false);		
			$response = curl_exec($feedbackconnect);
			curl_close($feedbackconnect);			
			$response = json_decode($response);
			if(isset($response->access_token)) {
				$store_ids = Mage::getModel('feedbackcompany/productreviews')->getAllStoreViews($storeid);
				$config = new Mage_Core_Model_Config();
				foreach($store_ids as $store_id) {
					$config->saveConfig('feedbackcompany/productreviews/client_token', $response->access_token, 'stores', $store_id);	
				}
				Mage::app()->getCacheInstance()->cleanType('config');
				$result = array();
				$result['status'] = 'OK';
				$result['client_token'] = $response->access_token;
				return $result;
			} else {
				if($response->description) {
					$result = array();
					$result['status'] = 'ERROR';
					$result['error'] = $response->description;
					return $result;
				}
			}			
		} else {
			return false;
		}	
	}
	
}