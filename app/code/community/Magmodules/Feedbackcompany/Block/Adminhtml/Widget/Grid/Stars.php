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
 
class Magmodules_Feedbackcompany_Block_Adminhtml_Widget_Grid_Stars extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

	public function render(Varien_Object $row) 
	{
		$value = $row->getData($this->getColumn()->getIndex());

		if($value == '0') {
			$output = ''; 
		} else {
			$output = '<span class="rating-empty"><span class="rating-star-' . $value . '"></span></span>';
			$output .= '<a href="#" class="magtooltip" alt="">(i)<span>';

			if($row->getData('score_aftersales') > 0) {
				$output .= '<strong>' . Mage::helper('feedbackcompany')->__('Aftersales:') . '</strong> ' . $row->getData('score_aftersales') . '/5<br>';
			}
			if($row->getData('score_checkout') > 0) {
				$output .= '<strong>' . Mage::helper('feedbackcompany')->__('Checkout process:') . '</strong> ' . $row->getData('score_checkout') . '/5<br>';
			}
			if($row->getData('score_information' > 0)) {
				$output .= '<strong>' . Mage::helper('feedbackcompany')->__('Information:') . '</strong> ' . $row->getData('score_information') . '/5<br>';
			}
			if($row->getData('score_friendly') > 0) {
				$output .= '<strong>' . Mage::helper('feedbackcompany')->__('Customer Friendlyness:') . '</strong> ' . $row->getData('score_friendly') . '/5<br>';
			}
			if($row->getData('score_leadtime') > 0) {
				$output .= '<strong>' . Mage::helper('feedbackcompany')->__('Leadtime:') . '</strong> ' . $row->getData('score_leadtime') . '/5<br>';
			}
			if($row->getData('score_responsetime') > 0) {
				$output .= '<strong>' . Mage::helper('feedbackcompany')->__('Repsonsetime:') . '</strong> ' . $row->getData('score_responsetime') . '/5<br>';
			}
			if($row->getData('score_order') > 0) {
				$output .= '<strong>' . Mage::helper('feedbackcompany')->__('Order process:') . '</strong> ' . $row->getData('score_order') . '/5<br>';
			}

			$output .= '<br/>';

			if($row->getData('text_positive')) {
				$output .= '<strong>' . Mage::helper('feedbackcompany')->__('Strong:') . '</strong> ' . $row->getData('text_positive') .'<br>';
			}
			if($row->getData('text_improvements')) {
				$output .= '<strong>' . Mage::helper('feedbackcompany')->__('Can do better:') . '</strong> ' . $row->getData('text_improvements') .'<br>';
			}

			$output .= '</span></a>';
		}

		return $output;
	}

}