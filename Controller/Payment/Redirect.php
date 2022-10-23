<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Controller\Payment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

class Redirect implements HttpGetActionInterface
{
    protected CheckoutSession $checkoutSession;
    protected MessageManagerInterface $messageManager;
    protected ResultFactory $resultFactory;
    protected SerializerInterface $serializer;
    protected OrderRepository $orderRepository;

    public function __construct(
        ResultFactory                     $resultFactory,
        MessageManagerInterface $messageManager,
        CheckoutSession $session,
        OrderRepository $orderRepository,
        SerializerInterface $serializer
    ) {
        $this->checkoutSession = $session;
        $this->messageManager  = $messageManager;
        $this->resultFactory  = $resultFactory;
        $this->serializer = $serializer;
        $this->orderRepository = $orderRepository;
    }

    public function execute(): ResultRedirect
    {
        try {
            $order = $this->getOrder();
            $payment = $order->getPayment();
            $additional = $payment->getAdditionalData();
            if ($additional) {
                $additionalData = $this->serializer->unserialize($additional);
                if (isset($additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE])) {
                    $quickPayLink = $additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE];
                    return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($quickPayLink);
                } else {
                    $this->messageManager->addErrorMessage(__('Payment link is not set.'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('Payment link is not set.'));
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->checkoutSession->restoreQuote();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong, please try again later'));
            $this->checkoutSession->restoreQuote();
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart');
    }

    /**
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function getOrder(): Order
    {
        $orderId = $this->checkoutSession->getLastOrderId();
        return $this->orderRepository->get($orderId);
    }
}
