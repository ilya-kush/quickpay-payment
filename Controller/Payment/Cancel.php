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
    protected CheckoutSession $checkoutSession;
    protected MessageManagerInterface $messageManager;
    protected ResultFactory $resultFactory;
    protected Data $helper;
    protected RequestInterface $request;
    protected OrderFactory $orderFactory;
    protected OrderService $orderService;

    public function __construct(
        RequestInterface        $request,
        ResultFactory           $resultFactory,
        MessageManagerInterface $messageManager,
        CheckoutSession         $session,
        Data                    $helper,
        OrderFactory            $orderFactory,
        OrderService            $orderService
    ) {
        $this->checkoutSession = $session;
        $this->messageManager  = $messageManager;
        $this->resultFactory  = $resultFactory;
        $this->helper = $helper;
        $this->request = $request;
        $this->orderFactory = $orderFactory;
        $this->orderService = $orderService;
    }

    public function execute(): Redirect
    {
        $this->processPaymentCancelation();
        return $this->redirectToCheckout();
    }

    protected function processPaymentCancelation(): void
    {
        if ($this->request->getParam('order')) {
            $orderIncrementId = $this->request->getParam('order');
        } else {
            /** This part to support payment links created in old version on module*/
            $orderIncrementId = $this->checkoutSession->getLastRealOrderId();
        }

        /** @var Order $order */
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        if ($order->getId()) {
            $this->orderService->cancel($order->getId());
            if ($orderIncrementId == $this->checkoutSession->getLastRealOrderId()) {
                $this->checkoutSession->restoreQuote();
            } else {
                $this->messageManager->addSuccessMessage(__('Your order has been canceled.'));
            }
            return;
        }
        $this->messageManager->addErrorMessage(sprintf(
            "%s: %s",
            $orderIncrementId,
            __("The entity that was requested doesn't exist. Verify the entity and try again.")
        ));
    }

    protected function redirectToCheckout(): Redirect
    {
        $params = ['_fragment' => 'payment'];
        if (!$this->helper->isOneStepCheckout()) {
            $params = [];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout', $params);
    }
}
