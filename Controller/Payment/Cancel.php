<?php
/**
 *  Cancel
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    09.11.2021
 * Time:    10:41
 */
namespace HW\QuickPay\Controller\Payment;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\OrderService;
use Magento\Sales\Model\OrderFactory;
use HW\QuickPay\Helper\Data;
/**
 *
 */
class Cancel implements HttpGetActionInterface {
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;
    /**
     * @var MessageManagerInterface
     */
    protected $_messageManager;

    protected ResultFactory $_resultFactory;
    /**
     * @var Data
     */
    protected $_helper;

    protected RequestInterface $_request;
    /**
     * @var OrderFactory
     */
    protected $_orderFactory;
    protected OrderService $_orderService;

    /**
     * @param RequestInterface        $request
     * @param ResultFactory           $resultFactory
     * @param MessageManagerInterface $messageManager
     * @param CheckoutSession         $session
     * @param Data                    $helper
     * @param OrderFactory            $orderFactory
     * @param OrderService            $orderService
     */
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

	/**
	 * @inheritDoc
	 */
	public function execute() {
        $this->_processPaymentCancelation();
        return $this->_redirectToCheckout();
	}

    /**
     *
     */
    protected function _processPaymentCancelation(){

        if($this->_request->getParam('order')){
            $orderIncrementId = $this->_request->getParam('order');
        } else {
            /** This part to support payment links created in old version on module*/
            $orderIncrementId = $this->_checkoutSession->getLastRealOrderId();
        }

        /** @var Order $order */
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);
        if($order->getId()){
            $this->_orderService->cancel($order->getId());
            if($orderIncrementId == $this->_checkoutSession->getLastRealOrderId()){
                $this->_checkoutSession->restoreQuote();
            } else {
                $this->_messageManager->addSuccessMessage(__('Your order has been canceled.'));
            }

            return;
        }
        $this->_messageManager->addErrorMessage(sprintf("%s: %s", $orderIncrementId, __("The entity that was requested doesn't exist. Verify the entity and try again.")));
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _redirectToCheckout(){
        $params = ['_fragment' => 'payment'];
        if(!$this->_helper->isOneStepCheckout()){
            $params = [];
        }
        return $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout',$params);
    }
}
