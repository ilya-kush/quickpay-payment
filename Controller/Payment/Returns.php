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
    protected CheckoutSession $checkoutSession;
    protected MessageManagerInterface $messageManager;
    protected ResultFactory $resultFactory;
    protected RequestInterface $request;

    public function __construct(
        RequestInterface        $request,
        ResultFactory           $resultFactory,
        MessageManagerInterface $messageManager,
        CheckoutSession         $session
    ) {
        $this->checkoutSession = $session;
        $this->messageManager  = $messageManager;
        $this->resultFactory  = $resultFactory;
        $this->request = $request;
    }

    public function execute(): ResultRedirect
    {
        if ($this->request->getParam('order')) {
            $orderIncrementId = $this->request->getParam('order');
        } else {
            $orderIncrementId = $this->checkoutSession->getLastRealOrderId();
        }

        if ($orderIncrementId && ($orderIncrementId == $this->checkoutSession->getLastRealOrderId())) {
            return $this->redirect('checkout/onepage/success');
        } else {
            /** todo: implement fishing success page */
            $this->messageManager->addSuccessMessage(
                __('Thank you for your purchase. You will soon receive a confirmation by email.')
            );
            return $this->redirect('checkout/cart');
        }
    }

    protected function redirect(string $path): ResultRedirect
    {
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path);
    }
}
