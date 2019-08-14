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
 
class Magmodules_Feedbackcompany_Adminhtml_FeedbackreviewsController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() 
	{
		$this->loadLayout()->_setActiveMenu('feedbackcompany/feedbackreviews')->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		return $this;
	}
 
	public function indexAction() 
	{
		$this->_initAction()->renderLayout();
	}

	public function processAction() 
	{
		$storeids = Mage::getModel('feedbackcompany/api')->getStoreIds();
		$start_time = microtime(true);
		foreach($storeids as $storeid)  {
			$msg = '';
			$api_id = Mage::getStoreConfig('feedbackcompany/general/api_id', $storeid);
			$result = Mage::getModel('feedbackcompany/api')->processFeed($storeid, 'all');		
			$log = Mage::getModel('feedbackcompany/log')->addToLog('reviews', $storeid, $result, '', (microtime(true) - $start_time), '', '');

			if(($result['review_new'] > 0) || ($result['review_updates'] > 0) || ($result['stats'] == true)) {
				$msg = Mage::helper('feedbackcompany')->__('Webwinkel ID %s:', $api_id) . ' '; 
				$msg .= Mage::helper('feedbackcompany')->__('%s new review(s)', $result['review_new']) . ', '; 
				$msg .= Mage::helper('feedbackcompany')->__('%s review(s) updated', $result['review_updates']) . ' & '; 
				$msg .= Mage::helper('feedbackcompany')->__('and total score updated.');
			}

			if($msg) {
				Mage::getSingleton('adminhtml/session')->addSuccess($msg);
			} else {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('feedbackcompany')->__('Webwinkel ID %s: no updates found, feed is empty or not found!', $api_id));
			}
		}
		Mage::getModel('feedbackcompany/stats')->processOverall();
		Mage::getModel('feedbackcompany/reviews')->flushCache();
		$this->_redirect('adminhtml/system_config/edit/section/feedbackcompany');
	}


	public function massDisableAction() 
	{
		$reviewIds = $this->getRequest()->getParam('reviewids');
		if(!is_array($reviewIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('feedbackcompany')->__('Please select item(s)'));
		} else {
			try {
				foreach ($reviewIds as $review_id) {
					$reviews = Mage::getModel('feedbackcompany/reviews')->load($review_id);
					$reviews->setStatus(0)->save();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('feedbackcompany')->__('Total of %d review(s) were disabled.', count($reviewIds)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		Mage::getModel('feedbackcompany/reviews')->flushCache();
		$this->_redirect('*/*/index');
	}

	public function massEnableAction() 
	{
	$reviewIds = $this->getRequest()->getParam('reviewids');
		if(!is_array($reviewIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('feedbackcompany')->__('Please select item(s)'));
		} else {
			try {
				foreach ($reviewIds as $review_id) {
					$reviews = Mage::getModel('feedbackcompany/reviews')->load($review_id);
					$reviews->setStatus(1)->save();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('feedbackcompany')->__('Total of %d review(s) were enabled.', count($reviewIds)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		Mage::getModel('feedbackcompany/reviews')->flushCache();
		$this->_redirect('*/*/index');
	}

	public function massEnableSidebarAction() 
	{
		$reviewIds = $this->getRequest()->getParam('reviewids');
		if(!is_array($reviewIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('feedbackcompany')->__('Please select item(s)'));
		} else {
			try {
				foreach ($reviewIds as $review_id) {
					$reviews = Mage::getModel('feedbackcompany/reviews')->load($review_id);
					$reviews->setSidebar(1)->save();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('feedbackcompany')->__('Total of %d review(s) were added to the sidebar.', count($reviewIds)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		Mage::getModel('feedbackcompany/reviews')->flushCache();
		$this->_redirect('*/*/index');
	}

	public function massDisableSidebarAction() 
	{
		$reviewIds = $this->getRequest()->getParam('reviewids');
		if(!is_array($reviewIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('feedbackcompany')->__('Please select item(s)'));
		} else {
			try {
				foreach ($reviewIds as $review_id) {
					$reviews = Mage::getModel('feedbackcompany/reviews')->load($review_id);
					$reviews->setSidebar(0)->save();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('feedbackcompany')->__('Total of %d review(s) were removed from the sidebar.', count($reviewIds)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		Mage::getModel('feedbackcompany/reviews')->flushCache();
		$this->_redirect('*/*/index');
	} 

	public function truncateAction() 
	{
		$i = 0;
		$collection = Mage::getModel('feedbackcompany/reviews')->getCollection();
		foreach ($collection as $item) {
			$item->delete();
			$i++;
		}
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('feedbackcompany')->__('Succefully deleted all %s saved review(s).', $i));
		Mage::getModel('feedbackcompany/reviews')->flushCache();
		$this->_redirect('*/*/index');	
	}

	public function productreviewsAction() 
	{
		$storeids = Mage::getModel('feedbackcompany/api')->getStoreIds('oauth');		
		$start_time = microtime(true); $qty = 0; $errors = array();
		foreach($storeids as $storeid) {
			$enabled = Mage::getStoreConfig('feedbackcompany/general/enabled', $storeid);
			$reviews_enabled = Mage::getStoreConfig('feedbackcompany/productreviews/enabled', $storeid);
			$client_id = Mage::getStoreConfig('feedbackcompany/productreviews/client_id', $storeid);			
			$client_secret = Mage::getStoreConfig('feedbackcompany/productreviews/client_secret', $storeid);						
			if($enabled && $reviews_enabled && !empty($client_id) && !empty($client_secret)) {
				$feed = Mage::getModel('feedbackcompany/api')->getFeed($storeid, 'productreviews', 'last_month');				
				if($feed['status'] == 'OK') {
					$results = Mage::getModel('feedbackcompany/productreviews')->processFeed($feed, $storeid);
					if($results['review_new'] > 0) {
						$qty = ($qty + $results['review_new']);
						$log = Mage::getModel('feedbackcompany/log')->addToLog('productreviews', $storeid, $results, '', (microtime(true) - $start_time), '');
					}						
				} else {					
					$errors[$client_id] = $feed['error'];
				}
			}			
		}
		if(count($errors) > 0) {
			foreach($errors as $key => $value) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('feedbackcompany')->__('API Response for client ID: %s => %s', $key, $value));		
			}	
		} else {
			if($qty > 0) {
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('feedbackcompany')->__('Imported %d new productreview(s).', $qty));
			} else {
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('feedbackcompany')->__('No new reviews found.', $qty));		
			}
		}
		$this->_redirect('adminhtml/system_config/edit/section/feedbackcompany');		
	}


	public function exportCsvAction()
	{
    	$reviews = $this->getRequest()->getPost('reviews', array());
    	$filter = $this->getRequest()->getParam('filter');
		$store_id = '';
		if($filter) {
			$filter = parse_str(urldecode(base64_decode($filter)), $params);
			if(!empty($params['visible_in'])) {
				$store_id = $params['visible_in'];
			}	
		}
		if(empty($store_id) && (!Mage::app()->isSingleStoreMode())) {		
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('feedbackcompany')->__('Please select specific storeview in the grid before exporting the reviews.'));
			$this->_redirect('adminhtml/catalog_product_review');
		} else {
			$store = Mage::getModel('core/store')->load($store_id); 
			if($csv_data = Mage::getModel('feedbackcompany/export')->getFeed($reviews, $store_id)) {
				$file_name = 'product-reviews-' . strtolower($store->getName()) . '.csv';
		        $path = Mage::getBaseDir('var') . DS . 'export';
		        if (!is_dir($path)) {
        		    mkdir($path);
		        }
		        $file = $path . DS . $file_name;
				$csv = new Varien_File_Csv();
				$csv->saveData($file, $csv_data);
				$this->_prepareDownloadResponse($file_name, array('type' => 'filename', 'value' => $file));
			} else {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('feedbackcompany')->__('Error, could not export the csv file.'));
				$this->_redirect('adminhtml/catalog_product_review');
			}			
		}
	}

	protected function _isAllowed() 
	{
        return Mage::getSingleton('admin/session')->isAllowed('shopreview/feedbackcompany/feedbackcompany_reviews');
    }

}