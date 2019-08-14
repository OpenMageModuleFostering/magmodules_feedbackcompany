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

class Magmodules_Feedbackcompany_Model_System_Config_Source_Reviewstatus {

	public function toOptionArray() 
	{		
		$status = array();
		$status[] = array('value'=>'1', 'label'	=> Mage::helper('feedbackcompany')->__('Approved'));
		$status[] = array('value'=>'2', 'label'	=> Mage::helper('feedbackcompany')->__('Pending'));
		$status[] = array('value'=>'3', 'label'	=> Mage::helper('feedbackcompany')->__('Not Approved'));
		return $status;			
	}

} 