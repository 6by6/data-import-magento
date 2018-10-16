<?php

namespace SixBySix\Port\Factory;

use SixBySix\Port\Writer\Shipment;

/**
 * Class ProductWriterFactory.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author  Anthony Bates <anthony@wearejh.com>
 */
class ShipmentWriterFactory
{
    /**
     * @return Shipment
     */
    public function __invoke()
    {
        $orderModel = \Mage::getModel('sales/order');
        $transaction = \Mage::getModel('core/resource_transaction');
        $trackingModel = \Mage::getModel('sales/order_shipment_track');
        $options = [
            'send_shipment_email' => (bool) \Mage::getStoreConfig('sales_email/shipment/enabled'),
        ];

        return new Shipment(
            $orderModel,
            $transaction,
            $trackingModel,
            $options
        );
    }
}
