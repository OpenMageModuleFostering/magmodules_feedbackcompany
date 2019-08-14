<?php
/**
 * Magmodules.eu - http://www.magmodules.eu
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category      Magmodules
 * @package       Magmodules_Feedbackcompany
 * @author        Magmodules <info@magmodules.eu>
 * @copyright     Copyright (c) 2017 (http://www.magmodules.eu)
 * @license       http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magmodules_Feedbackcompany_Block_Adminhtml_Widget_Grid_Log
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{

    /**
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $msg = '';
        $type = $row->getType();

        if ($type == 'reviews') {
            $updates = '';
            if ($row->getReviewNew() > 0) {
                $msg .= Mage::helper('feedbackcompany')->__('%s new review(s)', $row->getReviewNew());
                $updates++;
            }

            if ($row->getReviewUpdate() > 0) {
                if ($updates > 0) {
                    $msg .= ', ';
                }

                $msg .= Mage::helper('feedbackcompany')->__('%s review(s) updated', $row->getReviewUpdate());
                $updates++;
            }

            if ($updates > 0) {
                $msg .= ' & ';
            }

            $msg .= Mage::helper('feedbackcompany')->__('total score updated');

            $response = $row->getResponse();

            if (!empty($response)) {
                $msg = $row->getResponse();
            }
        }

        if ($type == 'productreviews') {
            if ($row->getReviewNew() > 0) {
                $msg = Mage::helper('feedbackcompany')->__('%s new productreview(s) imported', $row->getReviewNew());
            } else {
                $msg = Mage::helper('feedbackcompany')->__('No new reviews');
            }
        }

        if ($type == 'invitation') {
            if ($row->getOrderId()) {
                $order = Mage::getModel('sales/order')->load($row->getOrderId());
                $incrementId = $order->getIncrementId();
                $orderUrl = Mage::helper('adminhtml')->getUrl(
                    "adminhtml/sales_order/view",
                    array('order_id' => $row->getOrderId())
                );
                $msg = Mage::helper('feedbackcompany')->__(
                    '%s - %s',
                    '<a href="' . $orderUrl . '">#' . $incrementId . '</a>', $row->getResponse()
                );
            }
        }

        return ucfirst($msg);
    }

}