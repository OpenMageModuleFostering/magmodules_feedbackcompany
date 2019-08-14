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
 * @category    Magmodules
 * @package     Magmodules_Feedbackcompany
 * @author      Magmodules <info@magmodules.eu)
 * @copyright   Copyright (c) 2016 (http://www.magmodules.eu)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Magmodules_Feedbackcompany_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getTotalScore() 
	{
		if(Mage::getStoreConfig('feedbackcompany/general/enabled')) {
			$shop_id = Mage::getStoreConfig('feedbackcompany/general/api_id');					
			$review_stats = Mage::getModel('feedbackcompany/stats')->load($shop_id, 'shop_id');				
			if($review_stats->getScore() > 0) {
				$review_stats->setPercentage($review_stats->getScore());
				$review_stats->setStarsQty(number_format(($review_stats->getScore() / 10), 1, ',', ''));			
				return $review_stats;
			}
		}		
	}

	public function getStyle($type = 'sidebar') 
	{	
		if($type == 'left') {
			return Mage::getStoreConfig('feedbackcompany/sidebar/left_style');
		}
		if($type == 'right') {
			return Mage::getStoreConfig('feedbackcompany/sidebar/right_style');
		}
		if($type == 'sidebar') {
			return Mage::getStoreConfig('feedbackcompany/block/sidebar_style');
		}
	}	

    public function getSnippetsEnabled($type = 'sidebar') 
    {    	
		if(Mage::app()->getRequest()->getRouteName() == 'feedbackcompany') {
			return false;
		} else {	
			switch ($type) {
				case 'left':
					return Mage::getStoreConfig('feedbackcompany/sidebar/left_snippets');
					break;
				case 'right':
					return Mage::getStoreConfig('feedbackcompany/sidebar/right_snippets');
					break;
				case 'sidebar':
					return Mage::getStoreConfig('feedbackcompany/block/sidebar_snippets');
					break;
				case 'small':
					return Mage::getStoreConfig('feedbackcompany/block/small_snippets');
					break;	
				case 'header':
					return Mage::getStoreConfig('feedbackcompany/block/header_snippets');
					break;								
				case 'medium':
					return Mage::getStoreConfig('feedbackcompany/block/medium_snippets');
					break;				
			}
		}
	}
	
    function getReviewsUrl($type) 
    {    			
		$link = '';		
		switch ($type) {
			case 'left':
				$link = Mage::getStoreConfig('feedbackcompany/sidebar/left_link');
				break;
			case 'right':
				$link = Mage::getStoreConfig('feedbackcompany/sidebar/right_link');
				break;
			case 'sidebar':
				$link = Mage::getStoreConfig('feedbackcompany/block/sidebar_link');
				break;
			case 'small':
				$link = Mage::getStoreConfig('feedbackcompany/block/small_link');
				break;	
			case 'header':
				$link = Mage::getStoreConfig('feedbackcompany/block/header_link');
				break;								
			case 'medium':
				$link = Mage::getStoreConfig('feedbackcompany/block/medium_link');
				break;				
		}
		if($link == 'internal') {
			return Mage::getBaseUrl() . 'feedbackcompany';
		}
		if($link == 'external') {
			return Mage::getStoreConfig('feedbackcompany/general/url');
		}
		return false; 			
	}	

    function getSidebarCollection($sidebar) 
    {		
		$enabled = ''; $qty = '5';
		if(Mage::getStoreConfig('feedbackcompany/general/enabled')) {	
			if($sidebar == 'left') {
				$qty = Mage::getStoreConfig('feedbackcompany/sidebar/left_qty');
				$enabled = Mage::getStoreConfig('feedbackcompany/sidebar/left');
			}
			if($sidebar == 'right') {
				$qty = Mage::getStoreConfig('feedbackcompany/sidebar/right_qty');
				$enabled = Mage::getStoreConfig('feedbackcompany/sidebar/right');
			}
			if($sidebar == 'sidebar') {
				$qty = Mage::getStoreConfig('feedbackcompany/block/sidebar_qty');
				$enabled = Mage::getStoreConfig('feedbackcompany/block/sidebar');
			}
		}
		if($enabled) {	
			$shop_id = Mage::getStoreConfig('feedbackcompany/general/api_id');		
			$reviews = Mage::getModel("feedbackcompany/reviews")->getCollection();
			$reviews->setOrder('date_created', 'DESC');
			$reviews->addFieldToFilter('status',1);
			$reviews->addFieldToFilter('sidebar',1);			
			$reviews->addFieldToFilter('shop_id', array('eq'=> array($shop_id)));			
			$reviews->setPageSize($qty);
			$reviews->load();			
			return $reviews;
		}
		return false;
    }

    function getLatestReview() 
    {					
		if(Mage::getStoreConfig('feedbackcompany/block/medium_review')) {
			$shop_id = Mage::getStoreConfig('feedbackcompany/general/api_id');		
			$review = Mage::getModel("feedbackcompany/reviews")->getCollection();
			$review->setOrder('date_created', 'DESC');
			$review->addFieldToFilter('status',1);
			$review->addFieldToFilter('review_text', array('notnull' => true));		
			$review->addFieldToFilter('shop_id', array('eq'=> array($shop_id)));				
			return $review->getFirstItem();
		}
		return false;
    }
        
    function formatContent($sidebarreview, $sidebar = 'left') 
    { 
		$content = $sidebarreview->getReviewText();		
		$char_limit = '';
		if($sidebar == 'left') {
			$char_limit = Mage::getStoreConfig('feedbackcompany/sidebar/left_lenght');
		}
		if($sidebar == 'right') {
			$char_limit = Mage::getStoreConfig('feedbackcompany/sidebar/right_lenght');
		}
		if($sidebar == 'sidebar') {
			$char_limit = Mage::getStoreConfig('feedbackcompany/block/sidebar_lenght');
		}
		if($sidebar == 'medium') {
			$char_limit = Mage::getStoreConfig('feedbackcompany/block/medium_lenght');
		}
		
		if($char_limit > 1) {
			$url = $this->getReviewsUrl($sidebar);
			$content = Mage::helper('core/string')->truncate($content, $char_limit, ' ...', $_remainder, false);    
			if($url) {
				$content .= ' <a href="' . $url . '" target="_blank">' . $this->__('Read More') . '</a>';
			}
		}	
    	return $content;
	}

    public function getBlockEnabled($type) 
    {
		if(Mage::getStoreConfig('feedbackcompany/general/enabled')) {	
			switch ($type) {
				case 'left':
					return Mage::getStoreConfig('feedbackcompany/sidebar/left');
					break;
				case 'right':
					return Mage::getStoreConfig('feedbackcompany/sidebar/right');
					break;
				case 'sidebar':
					return Mage::getStoreConfig('feedbackcompany/block/sidebar');
					break;
				case 'small':
					return Mage::getStoreConfig('feedbackcompany/block/small');
					break;
				case 'header':
					return Mage::getStoreConfig('feedbackcompany/block/header');
					break;									
				case 'medium':
					return Mage::getStoreConfig('feedbackcompany/block/medium');
					break;				
			}
		}
		return false;
	} 

	public function getHtmlStars($rating, $type = 'small') 
	{	
		$perc = $rating;
		$show = '';
		if($type == 'small') {
			$show = Mage::getStoreConfig('feedbackcompany/block/small_stars');
		}
		if($type == 'medium') {
			$show = Mage::getStoreConfig('feedbackcompany/block/medium_stars');
		}
		if($show) {
			$html  = '<div class="rating-box">';
			$html .= '	<div class="rating" style="width:' . $perc . '%"></div>';
			$html .= '</div>';
			return $html;
		}	
		return false;
	}		
	
	public function formatScoresReview($review) 
	{	
		$scoreValues = Array();
		$scoreValuesPossible = Array(
			'aftersales' => 'Aftersales',
			'checkout' => 'Checkout',
			'information' => 'Information',
			'friendly'	=> 'Friendlyness',
			'leadtime'	=> 'Leadtime',
			'responsetime' => 'Responsetime',
			'order'	=> 'Orderprocess');

		foreach($scoreValuesPossible as $key => $value){
			if($review->getData("score_" . $key) > 0 ){
				$scoreValues[$value] = $review->getData("score_" . $key) * 20;
			}
		}
		return $scoreValues;
	}	
	
}