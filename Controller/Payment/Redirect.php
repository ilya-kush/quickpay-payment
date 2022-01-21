<?php
/**
 * Redirect
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    08.11.2021
 * Time:    17:10
 */
namespace HW\QuickPay\Controller\Payment;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

/**
 * Class Redirect
 *
 * @package
 */
class Redirect  implements HttpGetActionInterface {
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;
    /**
     * @var MessageManagerInterface
     */
    protected $_messageManager;
    /**
     * @var ResultFactory
     */
    protected $_resultFactory;
    /**
     * @var SerializerInterface
     */
    protected $_serializer;
    /**
     * @var OrderRepository
     */
    protected $_orderRepository;

    /**
     * @param ResultFactory           $resultFactory
     * @param MessageManagerInterface $messageManager
     * @param CheckoutSession         $session
     * @param OrderRepository         $orderRepository
     * @param SerializerInterface     $serializer
     */
    public function __construct(
        ResultFactory                     $resultFactory,
        MessageManagerInterface $messageManager,
        CheckoutSession $session,
        OrderRepository $orderRepository,
        SerializerInterface $serializer
    ) {
        $this->_checkoutSession = $session;
        $this->_messageManager  = $messageManager;
        $this->_resultFactory = $resultFactory;
        $this->_serializer = $serializer;
        $this->_orderRepository = $orderRepository;
    }

    /**
	 * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
	 */
	public function execute() {
        try{
            $order = $this->getOrder();
            $payment = $order->getPayment();
            $additional = $payment->getAdditionalData();
            if($additional) {
                $additionalData = $this->_serializer->unserialize($additional);
                if(isset($additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE])){
                    $quickPayLink = $additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE];
                    return $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($quickPayLink);
                } else {
                    $this->_messageManager->addErrorMessage(__('Payment link is not set.'));
                }
            } else {
                $this->_messageManager->addErrorMessage(__('Payment link is not set.'));
            }

            } catch (\Exception $e){
            $this->_messageManager->addErrorMessage(__('Something went wrong, please try again later'));
            $this->_messageManager->addErrorMessage($e->getMessage());
            $this->_checkoutSession->restoreQuote();
        }
        return $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart');
	}

    /**
     * @return Order
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getOrder() {
        if ($orderId = $this->_checkoutSession->getLastOrderId()) {
            return $this->_orderRepository->get($orderId);
        }
        throw new NoSuchEntityException(__("The entity that was requested doesn't exist. Verify the entity and try again."));
    }
}
