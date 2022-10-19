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

class Redirect  implements HttpGetActionInterface
{
    protected CheckoutSession $_checkoutSession;
    protected MessageManagerInterface $_messageManager;
    protected ResultFactory $_resultFactory;
    protected SerializerInterface $_serializer;
    protected OrderRepository $_orderRepository;

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

	public function execute(): ResultRedirect
    {
        try {
            $order = $this->getOrder();
            $payment = $order->getPayment();
            $additional = $payment->getAdditionalData();
            if ($additional) {
                $additionalData = $this->_serializer->unserialize($additional);
                if (isset($additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE])) {
                    $quickPayLink = $additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE];
                    return $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($quickPayLink);
                } else {
                    $this->_messageManager->addErrorMessage(__('Payment link is not set.'));
                }
            } else {
                $this->_messageManager->addErrorMessage(__('Payment link is not set.'));
            }
        } catch (NoSuchEntityException $e) {
            $this->_messageManager->addErrorMessage($e->getMessage());
            $this->_checkoutSession->restoreQuote();
        } catch (\Exception $e) {
            $this->_messageManager->addErrorMessage(__('Something went wrong, please try again later'));
            $this->_checkoutSession->restoreQuote();
        }
        return $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart');
	}

    /**
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function getOrder(): Order
    {
        $orderId = $this->_checkoutSession->getLastOrderId();
        return $this->_orderRepository->get($orderId);
    }
}
