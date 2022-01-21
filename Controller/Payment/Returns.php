<?php
/**
 *  Returns
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    09.11.2021
 * Time:    14:15
 */
namespace HW\QuickPay\Controller\Payment;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
/**
 *
 */
class Returns implements HttpGetActionInterface {
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
     * @var RequestInterface
     */
    protected $_request;


    /**
     * @param RequestInterface        $request
     * @param ResultFactory           $resultFactory
     * @param MessageManagerInterface $messageManager
     * @param CheckoutSession         $session
     */
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
	/**
	 * @inheritDoc
	 */
	public function execute() {

        if($this->_request->getParam('order')){
            $orderIncrementId = $this->_request->getParam('order');
        } else {
            $orderIncrementId = $this->_checkoutSession->getLastRealOrderId();
        }

        if($orderIncrementId && ($orderIncrementId == $this->_checkoutSession->getLastRealOrderId())){
            return $this->_redirect('checkout/onepage/success');
        } else {
            /** todo: implement fishing success page */
            $this->_messageManager->addSuccessMessage(__('Thank you for your purchase. You will soon receive a confirmation by email.'));
            return $this->_redirect('checkout/cart');
        }
	}

    /**
     * @param string $path
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _redirect(string $path){
        return $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path);
    }
}
