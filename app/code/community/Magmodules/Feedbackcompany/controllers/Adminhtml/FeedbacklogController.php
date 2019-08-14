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
 
class Magmodules_Feedbackcompany_Adminhtml_FeedbacklogController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() 
	{
		$this->loadLayout()->_setActiveMenu('feedbackcompany/feedbackreviews')->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		return $this;
	}
 
	public function indexAction() 
	{
		$this->_initAction()->renderLayout();
	}

	public function massDeleteAction() 
	{
		$LogIds = $this->getRequest()->getParam('logids');
		if(!is_array($LogIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('feedbackcompany')->__('Please select item(s)'));
		} else {
			try {
				foreach ($LogIds as $id) {
					$log = Mage::getModel('feedbackcompany/log')->load($id)->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('feedbackcompany')->__('Total of %d log record(s) deleted.', count($LogIds)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function cleanAction() 
	{
		$i = 0;
		$logmodel = Mage::getModel('feedbackcompany/log');
		$logs = $logmodel->getCollection();
		foreach ($logs as $log) {
			$logmodel->load($log->getId())->delete();
			$i++;
		}
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('feedbackcompany')->__('Total of %s log record(s) deleted.', $i));
		$this->_redirect('*/*/index');
	}

	protected function _isAllowed()
	{
        return Mage::getSingleton('admin/session')->isAllowed('shopreview/feedbackcompany/feedbackcompany_log');
    }

}