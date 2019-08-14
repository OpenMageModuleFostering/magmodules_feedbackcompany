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
 
class Magmodules_Feedbackcompany_Model_Observer {

	public function processStats() 
	{
		$storeids = Mage::getModel('feedbackcompany/api')->getStoreIds();
		foreach($storeids as $storeid) { 
			$enabled = Mage::getStoreConfig('feedbackcompany/general/enabled', $storeid);
			$cron_enabled = Mage::getStoreConfig('feedbackcompany/reviews/cron', $storeid);
			if($enabled && $cron_enabled) {
				$crontype = 'stats';
				$start_time = microtime(true);
				$feed = Mage::getModel('feedbackcompany/api')->getFeed($storeid, $crontype);
				$resuls = array();
				$results['stats'] = Mage::getModel('feedbackcompany/stats')->processFeed($feed, $storeid);
				$results['company'] = $feed->company;
				$log = Mage::getModel('feedbackcompany/log')->addToLog('reviews', $storeid, $results, '', (microtime(true) - $start_time), $crontype);
			}
		}
	}

	public function processReviews() 
	{
		$storeids = Mage::getModel('feedbackcompany/api')->getStoreIds();
		foreach($storeids as $storeid)  {
			$enabled = Mage::getStoreConfig('feedbackcompany/general/enabled', $storeid);
			$cron_enabled = Mage::getStoreConfig('feedbackcompany/reviews/cron', $storeid);
			if($enabled && $cron_enabled) {
				$crontype = 'reviews';
				$start_time = microtime(true);
				$feed = Mage::getModel('feedbackcompany/api')->getFeed($storeid, $crontype);
				$results = Mage::getModel('feedbackcompany/reviews')->processFeed($feed, $storeid, $crontype);
				$results['stats'] = Mage::getModel('feedbackcompany/stats')->processFeed($feed, $storeid);
				$log = Mage::getModel('feedbackcompany/log')->addToLog('reviews', $storeid, $results, '', (microtime(true) - $start_time), $crontype);
			}
		}
	}

	public function processProductreviews() 
	{
		$storeids = Mage::getModel('feedbackcompany/api')->getStoreIds();
		foreach($storeids as $storeid)  {
			$enabled = Mage::getStoreConfig('feedbackcompany/general/enabled', $storeid);
			$reviews_enabled = Mage::getStoreConfig('feedbackcompany/productreviews/enabled', $storeid);
			$cron_enabled = Mage::getStoreConfig('feedbackcompany/productreviews/cron');
			if($enabled && $cron_enabled && $reviews_enabled) {
				$crontype = 'productreviews';
				$start_time = microtime(true);
				$feed = Mage::getModel('feedbackcompany/api')->getFeed($storeid, $crontype, 'last_month');
				if($feed['status'] == 'OK') {
					$results = Mage::getModel('feedbackcompany/productreviews')->processFeed($feed, $storeid);
					if($results['review_new'] > 0) {
						$log = Mage::getModel('feedbackcompany/log')->addToLog('productreviews', $storeid, $results, '', (microtime(true) - $start_time), $crontype);
					}						
				}
			}
		}
	}

	public function processHistory() 
	{
		$storeids = Mage::getModel('feedbackcompany/api')->getStoreIds();
		foreach($storeids as $storeid)  {
			$enabled = Mage::getStoreConfig('feedbackcompany/general/enabled', $storeid);
			$cron_enabled = Mage::getStoreConfig('feedbackcompany/reviews/cron', $storeid);
			if($enabled && $cron_enabled) {
				$crontype = 'history';
				$start_time = microtime(true); $storeid = 0;
				$feed = Mage::getModel('feedbackcompany/api')->getFeed($storeid, $crontype);
				$results = Mage::getModel('feedbackcompany/reviews')->processFeed($feed, $storeid, $crontype);
				$results['stats'] = Mage::getModel('feedbackcompany/stats')->processFeed($feed, $storeid);
				$log = Mage::getModel('feedbackcompany/log')->addToLog('reviews', $storeid, $results, '', (microtime(true) - $start_time), $crontype);
			}
		}	
	}

	public function cleanLog() 
	{
		$enabled = Mage::getStoreConfig('feedbackcompany/log/clean', 0);
		$days = Mage::getStoreConfig('feedbackcompany/log/clean_days', 0);
		if(($enabled) && ($days > 0)) {
			$logmodel = Mage::getModel('feedbackcompany/log');
			$deldate = date('Y-m-d', strtotime('-' . $days . ' days'));
			$logs = $logmodel->getCollection()->addFieldToSelect('id')->addFieldToFilter('date', array('lteq' => $deldate));
			foreach ($logs as $log) {
				$logmodel->load($log->getId())->delete();
			}
		}
	}

    public function processFeedbackInvitationcallAfterShipment($observer) 
    {
		$shipment = $observer->getEvent()->getShipment();
		$order = $shipment->getOrder();
		if((Mage::getStoreConfig('feedbackcompany/invitation/enabled', $order->getStoreId())) && (Mage::getStoreConfig('feedbackcompany/invitation/connector', $order->getStoreId()))) {
			if($order->getStatus() == Mage::getStoreConfig('feedbackcompany/invitation/status', $order->getStoreId())) {
				if(!$order->getFeedbackSent()) {
					if(Mage::getStoreConfig('feedbackcompany/invitation/backlog', $order->getStoreId()) > 0) {
						$date_diff = floor(time() - strtotime($order->getCreatedAt()))/(60*60*24);
						if($date_diff < Mage::getStoreConfig('feedbackcompany/invitation/backlog', $order->getStoreId())) {
							Mage::getModel('feedbackcompany/api')->sendInvitation($order);
						}
					} else {
						Mage::getModel('feedbackcompany/api')->sendInvitation($order);
					}
				}
			}
		}
	}

	public function processFeedbackInvitationcall($observer) 
	{
		$order = $observer->getEvent()->getOrder();
		if((Mage::getStoreConfig('feedbackcompany/invitation/enabled', $order->getStoreId())) && (Mage::getStoreConfig('feedbackcompany/invitation/connector', $order->getStoreId()))) {
			if($order->getStatus() == Mage::getStoreConfig('feedbackcompany/invitation/status', $order->getStoreId())) {
				if(!$order->getFeedbackSent()) {
					if(Mage::getStoreConfig('feedbackcompany/invitation/backlog', $order->getStoreId()) > 0) {
						$date_diff = floor(time() - strtotime($order->getCreatedAt()))/(60*60*24);
						if($date_diff < Mage::getStoreConfig('feedbackcompany/invitation/backlog', $order->getStoreId())) {
							$value = Mage::getModel('feedbackcompany/api')->sendInvitation($order);
						}
					} else {
						Mage::getModel('feedbackcompany/api')->sendInvitation($order);
					}
				}
			}
		}
	}

	public function addExportOption($observer) 
	{
   		$block = $observer->getEvent()->getBlock();
		if(get_class($block) == 'Mage_Adminhtml_Block_Widget_Grid_Massaction' && $block->getRequest()->getControllerName() == 'catalog_product_review') {        			
			$request = Mage::app()->getFrontController()->getRequest();
        	$block->addItem('reviewsexport', array(
				'label' => Mage::helper('feedbackcompany')->__('Export Reviews'),
				'url' => Mage::app()->getStore()->getUrl('*/feedbackreviews/exportcsv/filter/' . $request->getParam('filter')),
			));
        }
	}

}