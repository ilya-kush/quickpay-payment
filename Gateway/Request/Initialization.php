<?php
/**
 *  Initialization
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    12.10.2021
 * Time:    13:16
 */
namespace HW\QuickPay\Gateway\Request;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\Method\Logger;
use HW\QuickPay\Gateway\Helper\AmountConverter;
use HW\QuickPay\Helper\Data;
use Zend_Locale;
/**
 *
 */
class Initialization extends AbstractRequest {
    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $_moduleResource;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadata;

    /**
     * @param SerializerInterface                             $serializer
     * @param Data                                            $helper
     * @param Logger                                          $logger
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Module\ResourceInterface     $moduleResource
     */
    public function __construct(
        SerializerInterface                             $serializer,
        Data                                            $helper,
        AmountConverter                                 $amountConverter,
        Logger                                          $logger,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ResourceInterface     $moduleResource) {
        parent::__construct($serializer, $helper, $amountConverter,$logger);
        $this->_moduleResource  = $moduleResource;
        $this->_productMetadata = $productMetadata;
    }

    /**
	 * @inheritDoc
	 */
	public function build(array $buildSubject) {

        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];

        /** @var OrderAdapter $order */
        $order   = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();
        $storeId = $order->getStoreId();

        /** Required fields */
        $paymentParameters = [
            'order_id' => $order->getOrderIncrementId(),
            'currency' => $order->getCurrencyCode(),
        ];

        /** Additional fields */
        $paymentParameters["test_mode"] = $this->_helper->isTestMode($storeId);
        if ($textOnStatement = $this->_helper->getTextOnStatement($storeId)) {
            $paymentParameters['text_on_statement'] = $textOnStatement;
        }

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $paymentParameters['shipping_address'] = [];
            $paymentParameters['shipping_address']['name'] = trim(sprintf("%s %s", $shippingAddress->getFirstname(), $shippingAddress->getLastname()));
            $paymentParameters['shipping_address']['street'] = trim(sprintf("%s %s", $shippingAddress->getStreetLine1(), $shippingAddress->getStreetLine2()));
            $paymentParameters['shipping_address']['city'] = $shippingAddress->getCity();
            $paymentParameters['shipping_address']['zip_code'] = $shippingAddress->getPostcode();
            $paymentParameters['shipping_address']['region'] = $shippingAddress->getRegionCode();
            $paymentParameters['shipping_address']['country_code'] = Zend_Locale::getTranslation($shippingAddress->getCountryId(), 'Alpha3ToTerritory');
            $paymentParameters['shipping_address']['phone_number'] = $shippingAddress->getTelephone();
            $paymentParameters['shipping_address']['email'] = $shippingAddress->getEmail();
            $paymentParameters['shipping_address']['house_number'] = '';
            $paymentParameters['shipping_address']['house_extension'] = '';
            $paymentParameters['shipping_address']['mobile_number'] = $shippingAddress->getTelephone();
        }

        $billingAddress = $order->getBillingAddress();
        $paymentParameters['invoice_address'] = [];
        $paymentParameters['invoice_address']['name'] = trim(sprintf("%s %s", $billingAddress->getFirstname(), $billingAddress->getLastname()));
        $paymentParameters['invoice_address']['street'] =  trim(sprintf("%s %s", $billingAddress->getStreetLine1(), $billingAddress->getStreetLine2()));
        $paymentParameters['invoice_address']['city'] = $billingAddress->getCity();
        $paymentParameters['invoice_address']['zip_code'] = $billingAddress->getPostcode();
        $paymentParameters['invoice_address']['region'] = $billingAddress->getRegionCode();
        $paymentParameters['invoice_address']['country_code'] = Zend_Locale::getTranslation($billingAddress->getCountryId(), 'Alpha3ToTerritory');
        $paymentParameters['invoice_address']['phone_number'] = $billingAddress->getTelephone();
        $paymentParameters['invoice_address']['email'] = $billingAddress->getEmail();
        $paymentParameters['invoice_address']['house_number'] = '';
        $paymentParameters['invoice_address']['house_extension'] = '';
        $paymentParameters['invoice_address']['mobile_number'] = $billingAddress->getTelephone();

        //Build basket array
        $paymentParameters['basket'] = [];

        foreach ($order->getItems() as $item) {
            if (!$item->getParentItem()) {
                $paymentParameters['basket'][] = [
                    'qty'       => (int)$item->getQtyOrdered(),
                    'item_no'   => $item->getSku(),
                    'item_name' => $item->getName(),
                    'item_price'=> $this->_amountConverter->convert($item->getPriceInclTax()),
                    'vat_rate'  => $item->getTaxPercent() ? $this->_amountConverter->backConvert($item->getTaxPercent()): 0
                ];
            }
        }

        /** todo: look for a way to add shipping information */
        $paymentParameters['shipping'] = [
            'amount'   => $this->_amountConverter->convert($payment->getShippingAmount()),
//            'method'   => $order->getShippingMethod(true)->getMethod(),
//            'company'  => $order->getShippingMethod(true)->getCarrierCode(),
//            'vat_rate' => ($order->getShippingTaxAmount() * 100) / $order->getShippingInclTax()
        ];

        /** add data about our module */
        $paymentParameters['shopsystem'] = [];
        $paymentParameters['shopsystem']['name'] = sprintf(
            '%s %s %s (%s)',
            $this->_productMetadata->getName(),
            $this->_productMetadata->getVersion(),
            $this->_productMetadata->getEdition(),
            $this->_helper->getModuleName()
        );
        $paymentParameters['shopsystem']['version'] = $this->_moduleResource->getDbVersion($this->_helper->getModuleName());

        return [
            'payment' => $paymentParameters
        ];
	}
}
