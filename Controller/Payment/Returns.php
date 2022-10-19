<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Controller\Payment;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class Returns implements HttpGetActionInterface
{
    protected CheckoutSession $_checkoutSession;
    protected MessageManagerInterface $_messageManager;
    protected ResultFactory $_resultFactory;
    protected RequestInterface $_request;

    public function __construct(
        RequestInterface        $request,
        ResultFactory           $resultFactory,
        MessageManagerInterface $messageManager,
        CheckoutSession         $session
    ) {
        $this->_checkoutSession = $session;
        $this->_messageManager  = $messageManager;
        $this->_resultFactory = $resultFactory;
        $this->_request = $request;
    }

	public function execute(): ResultRedirect
    {
        if ($this->_request->getParam('order')) {
            $orderIncrementId = $this->_request->getParam('order');
        } else {
            $orderIncrementId = $this->_checkoutSession->getLastRealOrderId();
        }

        if($orderIncrementId && ($orderIncrementId == $this->_checkoutSession->getLastRealOrderId())) {
            return $this->_redirect('checkout/onepage/success');
        } else {
            /** todo: implement fishing success page */
            $this->_messageManager->addSuccessMessage(
                __('Thank you for your purchase. You will soon receive a confirmation by email.'));
            return $this->_redirect('checkout/cart');
        }
	}

    protected function _redirect(string $path): ResultRedirect
    {
        return $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path);
    }
}
