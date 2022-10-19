<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Controller\Payment;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\OrderService;
use Magento\Sales\Model\OrderFactory;
use HW\QuickPay\Helper\Data;

class Cancel implements HttpGetActionInterface
{
    protected CheckoutSession $_checkoutSession;
    protected MessageManagerInterface $_messageManager;
    protected ResultFactory $_resultFactory;
    protected Data $_helper;
    protected RequestInterface $_request;
    protected OrderFactory $_orderFactory;
    protected OrderService $_orderService;

    public function __construct(
        RequestInterface        $request,
        ResultFactory           $resultFactory,
        MessageManagerInterface $messageManager,
        CheckoutSession         $session,
        Data                    $helper,
        OrderFactory            $orderFactory,
        OrderService            $orderService
    ) {
        $this->_checkoutSession = $session;
        $this->_messageManager  = $messageManager;
        $this->_resultFactory = $resultFactory;
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_orderFactory = $orderFactory;
        $this->_orderService = $orderService;
    }

	public function execute(): Redirect
    {
        $this->_processPaymentCancelation();
        return $this->_redirectToCheckout();
	}

    protected function _processPaymentCancelation(): void
    {
        if ($this->_request->getParam('order')) {
            $orderIncrementId = $this->_request->getParam('order');
        } else {
            /** This part to support payment links created in old version on module*/
            $orderIncrementId = $this->_checkoutSession->getLastRealOrderId();
        }

        /** @var Order $order */
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);
        if($order->getId()) {
            $this->_orderService->cancel($order->getId());
            if($orderIncrementId == $this->_checkoutSession->getLastRealOrderId()) {
                $this->_checkoutSession->restoreQuote();
            } else {
                $this->_messageManager->addSuccessMessage(__('Your order has been canceled.'));
            }
            return;
        }
        $this->_messageManager->addErrorMessage(sprintf(
            "%s: %s",
            $orderIncrementId,
            __("The entity that was requested doesn't exist. Verify the entity and try again.")
            )
        );
    }

    protected function _redirectToCheckout(): Redirect
    {
        $params = ['_fragment' => 'payment'];
        if(!$this->_helper->isOneStepCheckout()) {
            $params = [];
        }
        return $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout',$params);
    }
}
