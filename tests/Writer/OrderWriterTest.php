<?php

namespace SixBySix\PortTest\Writer;

use SixBySix\Port\Writer\OrderWriter;

/**
 * Class OrderWriterTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class OrderWriterTest extends \PHPUnit\Framework\TestCase
{
    protected $quote;
    protected $convertQuote;
    protected $customer;
    protected $product;
    protected $quoteItem;
    protected $writer;

    protected function setUp()
    {
        $this->quote = $this->getMockModel('\Mage_Sales_Model_Quote');
        $this->convertQuote = $this->getMockModel('\Mage_Sales_Model_Convert_Quote');
        $this->customer = $this->getMockModel('\Mage_Customer_Model_Customer');
        $this->product = $this->getMockModel('\Mage_Catalog_Model_Product');
        $this->quoteItem = $this->getMockModel('\Mage_Sales_Model_Quote_Item', true);

        $this->writer = new OrderWriter(
            $this->quote,
            $this->convertQuote,
            $this->customer,
            $this->product,
            $this->quoteItem
        );
    }

    public function testExceptionIsThrownIfMappingAttributeOrPaymentMethodIsNotString()
    {
        $this->expectException(
            'InvalidArgumentException'
        );
        $this->expectExceptionMessage(
            'Customer Mapping Attribute and Payment Method Code should be strings'
        );

        new OrderWriter(
            $this->quote,
            $this->convertQuote,
            $this->customer,
            $this->product,
            $this->quoteItem,
            new \stdClass()
        );
    }

    public function testCalculateSubTotal()
    {
        $orderMock = $this->getMockModel('\Mage_Sales_Model_Order');
        $item1 = new \Mage_Sales_Model_Order_Item();
        $item1->setData([
            'price' => 10,
            'tax_amount' => 20,
        ]);
        $item2 = new \Mage_Sales_Model_Order_Item();
        $item2->setData([
            'price' => 220,
            'tax_amount' => 20,
        ]);

        $orderMock
            ->expects($this->any())
            ->method('getAllItems')
            ->will($this->returnValue([
                $item1,
                $item2,
            ]));

        $this->assertSame(270, $this->writer->calculateSubTotal($orderMock));
    }

    public function testCalculateGrandTotal()
    {
        $data = [
            'shipping_amount' => 20,
            'gw_price' => 15,
            'discount_amount' => 10,
        ];

        $orderMock = $this->getMockModel('\Mage_Sales_Model_Order');
        $item1 = new \Mage_Sales_Model_Order_Item();
        $item1->setData([
            'price' => 10,
            'tax_amount' => 20,
        ]);
        $item2 = new \Mage_Sales_Model_Order_Item();
        $item2->setData([
            'price' => 220,
            'tax_amount' => 20,
        ]);

        $orderMock
            ->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue([
                $item1,
                $item2,
            ]));

        $this->assertSame(295, $this->writer->calculateGrandTotal($orderMock, $data));
    }

    public function testAddProductsToQuote()
    {
        $items = [
            ['sku' => 'SKU1', 'price' => 5, 'qty' => 2],
        ];

        $this->product
            ->expects($this->once())
            ->method('loadByAttribute')
            ->will($this->returnSelf());

        $this->quoteItem
            ->expects($this->once())
            ->method('setProduct')
            ->with($this->product);

        $this->quoteItem
            ->expects($this->once())
            ->method('setQuote')
            ->with($this->quote);

        $this->quoteItem
            ->expects($this->once())
            ->method('setQty')
            ->with(2);

        $this->quoteItem
            ->expects($this->once())
            ->method('addData')
            ->with([
                'price' => 5,
                'base_price' => 5,
                'original_price' => 5,
                'custom_price' => 5,
                'original_custom_price' => 5,
            ]);

        $this->quote
            ->expects($this->once())
            ->method('addItem')
            ->with($this->quoteItem);

        $this->writer->addProductsToQuote($this->quote, $items);
    }

    public function testAddProductsToQuoteThrowsExceptionIfProductNotFound()
    {
        $items = [
            ['sku' => 'SKU1', 'price' => 5, 'qty' => 2],
        ];

        $this->product
            ->expects($this->once())
            ->method('loadByAttribute')
            ->will($this->returnValue(null));

        $this->expectException(
            'Port\Exception\WriterException'
        );
        $this->expectExceptionMessage(
            'Product with SKU: SKU1 does not exist in Magento'
        );

        $this->writer->addProductsToQuote($this->quote, $items);
    }

    public function testAddCustomerToQuoteWithNoDefaultAddress()
    {
        $firstName = 'Aydin';
        $lastName = 'Hassan';
        $email = 'aydin@hotmail.co.uk';

        $this->customer
            ->method('getData')
            ->will($this->returnValueMap([
                ['firstname', null, $firstName],
                ['lastname', null, $lastName],
                ['email', null, $email],
            ]));

        $this->quote
            ->expects($this->once())
            ->method('addData')
            ->with([
                'customer_firstname' => $firstName,
                'customer_lastname' => $lastName,
                'customer_email' => $email,
            ]);

        $addressData = [
            'street' => 'Some Street',
            'city' => 'Some City',
        ];
        $address = $this->createMock('Mage_Customer_Model_Address');
        $address
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($addressData));

        $addressCollectionResource = $this->getMockModel('Mage_Customer_Model_Resource_Address_Collection');
        $addressCollectionResource
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($address));

        $this->customer
            ->expects($this->once())
            ->method('getAddressesCollection')
            ->will($this->returnValue($addressCollectionResource));

        $quoteAddress = $this->getMockModel('Mage_Sales_Model_Quote_Address');
        $quoteAddress
            ->expects($this->any())
            ->method('addData')
            ->with($addressData);

        $this->quote
            ->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($quoteAddress));

        $this->quote
            ->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($quoteAddress));

        $this->quote
            ->expects($this->once())
            ->method('assignCustomer')
            ->with($this->customer);

        $this->writer->addCustomerToQuote($this->quote, $this->customer);
    }

    public function testAddCustomerToQuoteWithDefaultAddress()
    {
        $firstName = 'Aydin';
        $lastName = 'Hassan';
        $email = 'aydin@hotmail.co.uk';

        $this->customer
            ->method('getData')
            ->will($this->returnValueMap([
                ['firstname', null, $firstName],
                ['lastname', null, $lastName],
                ['email', null, $email],
            ]));

        $this->quote
            ->expects($this->once())
            ->method('addData')
            ->with([
                'customer_firstname' => $firstName,
                'customer_lastname' => $lastName,
                'customer_email' => $email,
            ]);

        $addressData = [
            'street' => 'Some Street',
            'city' => 'Some City',
        ];
        $address = $this->createMock('Mage_Customer_Model_Address');
        $address
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($addressData));

        $this->customer
            ->expects($this->once())
            ->method('getDefaultBillingAddress')
            ->will($this->returnValue($address));

        $this->customer
            ->expects($this->once())
            ->method('getDefaultShippingAddress')
            ->will($this->returnValue($address));

        $quoteAddress = $this->getMockModel('Mage_Sales_Model_Quote_Address');
        $quoteAddress
            ->expects($this->any())
            ->method('addData')
            ->with($addressData);

        $this->quote
            ->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($quoteAddress));

        $this->quote
            ->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($quoteAddress));

        $this->quote
            ->expects($this->once())
            ->method('assignCustomer')
            ->with($this->customer);
        $this->writer->addCustomerToQuote($this->quote, $this->customer);
    }

    public function testAddDetailsToQuote()
    {
        $data = [
            'created_at' => '12-04-1988',
            'increment_id' => '000001',
        ];

        $payment = $this->getMockModel('Mage_Payment_Model_Method_Abstract');

        $payment
            ->expects($this->once())
            ->method('addData')
            ->with(['method' => 'checkmo']);

        $this->quote
            ->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($payment));

        $this->quote
            ->expects($this->once())
            ->method('addData')
            ->with([
                'created_at' => $data['created_at'],
                'reserved_order_id' => $data['increment_id'],
            ]);

        $address = $this->createMock('Mage_Customer_Model_Address');

        $address
            ->expects($this->once())
            ->method('addData')
            ->with(['payment_method' => 'checkmo']);

        $this->quote
            ->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($address));

        $this->writer->addDetailsToQuote($this->quote, $data);
    }

    public function testGetCustomerByAttribute()
    {
        $collection = $this->getMockModel('Mage_Customer_Model_Resource_Customer_Collection');

        $this->customer
            ->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($collection));

        $attribute = 'email';
        $value = 'aydin@hotmail.co.uk';

        $collection
            ->expects($this->once())
            ->method('addAttributeToFilter')
            ->with($attribute, $value)
            ->will($this->returnSelf());

        $collection
            ->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*')
            ->will($this->returnSelf());

        $customer = $this->getMockModel('Mage_Customer_Model_Customer');

        $collection
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($customer));

        $this->assertSame($customer, $this->writer->getCustomerByAttribute($attribute, $value));
    }

    public function testQuoteToOrder()
    {
        $order = $this->getMockModel('Mage_Sales_Model_Order');

        $this->convertQuote
            ->expects($this->once())
            ->method('toOrder')
            ->with($this->quote)
            ->will($this->returnValue($order));

        $quoteAddress1 = $this->getMockModel('Mage_Sales_Model_Quote_Address');
        $quoteAddress2 = $this->getMockModel('Mage_Sales_Model_Quote_Address');
        $orderAddress1 = $this->getMockModel('Mage_Sales_Model_Order_Address');
        $orderAddress2 = $this->getMockModel('Mage_Sales_Model_Order_Address');

        $this->quote
            ->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($quoteAddress1));

        $this->quote
            ->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($quoteAddress2));

        $this->convertQuote
            ->method('addressToOrderAddress')
            ->will($this->returnValueMap([
                [$quoteAddress1, $orderAddress1],
                [$quoteAddress2, $orderAddress2],
            ]));

        $order
            ->expects($this->once())
            ->method('setBillingAddress')
            ->with($orderAddress1);

        $order
            ->expects($this->once())
            ->method('setBillingAddress')
            ->with($orderAddress2);

        $item1 = new \Mage_Sales_Model_Order_Item();
        $item1->setData([
            'price' => 10,
            'tax_amount' => 20,
        ]);
        $item2 = new \Mage_Sales_Model_Order_Item();
        $item2->setData([
            'price' => 220,
            'tax_amount' => 20,
        ]);

        $order
            ->expects($this->any())
            ->method('getAllItems')
            ->will($this->returnValue([
                $item1,
                $item2,
            ]));

        $quoteItem = new \Mage_Sales_Model_Quote_Item();
        $quoteItem->addData(['sku' => 'SKU1']);
        $orderItem = $this->getMockModel('Mage_Sales_Model_Order_Item');

        $this->quote
            ->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue([$quoteItem]));

        $payment = $this->createMock('Mage_Sales_Model_Quote_Payment');
        $this->quote
            ->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($payment));

        $orderPayment = $this->createMock('Mage_Sales_Model_Order_Payment');
        $this->convertQuote
            ->expects($this->once())
            ->method('paymentToOrderPayment')
            ->will($this->returnValue($orderPayment));

        $this->convertQuote
            ->expects($this->once())
            ->method('itemToOrderItem')
            ->with($quoteItem)
            ->will($this->returnValue($orderItem));

        $orderData = [
            'items' => [
                [
                    'sku' => 'SKU1',
                    'discount_amount' => 6,
                    'tax_amount' => 20,
                    'gw_price' => 8,
                    'price' => 100,
                ],
            ],
            'shipping_amount' => 30,
            'gw_price' => 20,
            'discount_amount' => 20,
        ];

        $orderItem
            ->expects($this->once())
            ->method('addData')
            ->with([
                'discount_amount' => 6,
                'base_discount_amount' => 6,
                'tax_amount' => 20,
                'base_tax_amount' => 20,
                'gw_price' => 8,
                'base_gw_price' => 8,
                'tax_percent' => 20,
            ]);

        $this->writer->quoteToOrder($this->quote, $orderData);
    }

    protected function getMockModel($class, $disableClone = false)
    {
        $mockBuilder = $this->getMockBuilder($class)
            ->disableOriginalConstructor();

        if ($disableClone) {
            $mockBuilder->disableOriginalClone();
        }

        return $mockBuilder->getMock();
    }
}
