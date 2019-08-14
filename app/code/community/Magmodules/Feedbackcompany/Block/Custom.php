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
 
class Magmodules_Feedbackcompany_Block_Custom extends Mage_Core_Block_Template {

    protected function _construct() 
    {
	    parent::_construct();
	
		$blockType = $this->getData("blocktype");
		$blockTypeTemplate = '';
				
		if($blockType == 'sidebar') {
			$total = $this->helper('feedbackcompany')->getTotalScore();
			$enabled = $this->helper('feedbackcompany')->getBlockEnabled('sidebar');
			$sidebarreviews = $this->helper('feedbackcompany')->getSidebarCollection('sidebar');	
			if($total && $enabled && $sidebarreviews) {
		        $this->setTotals($total);
		        $this->setReviews($sidebarreviews);
		        $blockTypeTemplate = 'magmodules/feedbackcompany/widget/sidebar.phtml';
			}  
		}
		
		if($blockType == 'medium') {
			$total = $this->helper('feedbackcompany')->getTotalScore();
			$enabled = $this->helper('feedbackcompany')->getBlockEnabled('medium');
			if($total && $enabled) {
		        $this->setTotals($total);
		        $blockTypeTemplate = 'magmodules/feedbackcompany/widget/medium.phtml';
			}  
		}
		
		if($blockType == 'small') {
			$total = $this->helper('feedbackcompany')->getTotalScore();
			$enabled = $this->helper('feedbackcompany')->getBlockEnabled('small');
			if($total && $enabled) {
		        $this->setTotals($total);
		        $blockTypeTemplate = 'magmodules/feedbackcompany/widget/small.phtml';
			}  
		}

		if($blockType == 'summary') {
			$total = $this->helper('feedbackcompany')->getTotalScore();
			$enabled = $this->helper('feedbackcompany')->getBlockEnabled('summary');
			if($total && $enabled) {
		        $this->setTotals($total);
		        $blockTypeTemplate = 'magmodules/feedbackcompany/widget/summary.phtml';
			}  
		}
		
		if($blockTypeTemplate) {	

			$storeId = Mage::app()->getStore()->getStoreId();

			$this->addData(array(
				'cache_lifetime'    => 0,
				'cache_tags'        => array(Mage_Cms_Model_Block::CACHE_TAG, Magmodules_Feedbackcompany_Model_Reviews::CACHE_TAG),
				'cache_key'         => $storeId . '-' . $blockType . '-feedback-block',       
			));

	        parent::_construct();                                   
    	    $this->setTemplate($blockTypeTemplate);		
    	} 
    }
  
	public function getFeedbackcompanyData() 
	{
		return $this->helper('feedbackcompany')->getTotalScore();
    }	    

    function formatContent($sidebarreview, $sidebar) 
    {    	
		return $this->helper('feedbackcompany')->formatContent($sidebarreview, $sidebar);	
	}

    function getReviewsUrl($type) 
    {    	
		return $this->helper('feedbackcompany')->getReviewsUrl($type);	
	}

    public function getSnippetsEnabled($sidebar) 
    {    	
		return $this->helper('feedbackcompany')->getSnippetsEnabled($sidebar);	
	}

	public function getLatestReview() 
	{
		 return $this->helper('feedbackcompany')->getLatestReview();
    }    

	public function getHtmlStars($percentage, $type) 
	{
		 return $this->helper('feedbackcompany')->getHtmlStars($percentage, $type);
    } 
     
}