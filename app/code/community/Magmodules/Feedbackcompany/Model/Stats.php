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
 
class Magmodules_Feedbackcompany_Model_Stats extends Mage_Core_Model_Abstract {

	public function _construct() 
	{
		parent::_construct();
		$this->_init('feedbackcompany/stats');
	}

	public function processFeed($feed, $storeid = 0) 
	{
		$shop_id = Mage::getStoreConfig('feedbackcompany/general/api_id', $storeid);
		$company = Mage::getStoreConfig('feedbackcompany/general/company', $storeid);

		if($storeid == 0) {
			$config = new Mage_Core_Model_Config();
			$config->saveConfig('feedbackcompany/general/url', $feed->detailslink, 'default', $storeid);
		} else {
			$config = new Mage_Core_Model_Config();
			$config->saveConfig('feedbackcompany/general/url', $feed->detailslink, 'stores', $storeid);
			if(!Mage::getStoreConfig('feedbackcompany/general/url', 0)) {		
				$config->saveConfig('feedbackcompany/general/url', $feed->detailslink, 'default', 0);
			}
		}

		if($feed->noReviews > 0) {
			$score = floatval($feed->score);
			$score = ($score * 10);
			$scoremax = ($feed->scoremax * 10);
			$votes = $feed->noReviews;

			// Check for update or save
			if($indatabase = $this->loadbyShopId($shop_id)) {
				$id = $indatabase->getId();
			} else {
				$id = '';
			}

			// Save Review Stats
			$model = Mage::getModel('feedbackcompany/stats');
			$model->setId($id)
				->setShopId($shop_id)
				->setCompany($company)
				->setScore($score)
				->setScoremax($scoremax)
				->setVotes($votes)
				->save();
			return true;
		} else {
			return false;
		}
	}

	public function processOverall() 
	{
		$stats = Mage::getModel('feedbackcompany/stats')->getCollection();
		$stats->addFieldToFilter('shop_id', array('neq' => '0'));

		$score = '';
		$scoremax = '';
		$votes = '';
		$i = 0;

		foreach($stats as $stat) {
			$score = ($score + $stat->getScore());
			$scoremax = ($scoremax + $stat->getScoremax());
			$votes = ($votes + $stat->getVotes());
			$i++;
		}

		if($i > 0) {
			$score = ($score / $i); 
			$scoremax = ($scoremax / $i); 
			$company = 'Overall';
		}	

		if($indatabase = $this->loadbyShopId(0)) {
			$id = $indatabase->getId();
		} else {
			$id = '';
		}

		$model = Mage::getModel('feedbackcompany/stats')
			->setId($id)
			->setShopId(0)
			->setCompany($company)
			->setScore($score)
			->setScoremax($scoremax)
			->setVotes($votes)
			->save();
	}

	public function loadbyShopId($shop_id) 
	{
		$this->_getResource()->load($this, $shop_id, 'shop_id');
		return $this;
	}
	
}