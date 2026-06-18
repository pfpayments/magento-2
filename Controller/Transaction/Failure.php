<?php
/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @package PostFinanceCheckout_Payment
 * @author wallee AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)

 */
namespace PostFinanceCheckout\Payment\Controller\Transaction;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DataObject;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use PostFinanceCheckout\PluginCore\Transaction\TransactionService as CoreTransactionService;

/**
 * Frontend controller action to handle failed payments.
 */
class Failure extends \PostFinanceCheckout\Payment\Controller\Transaction
{

    /**
     * @var CoreTransactionService
     */
    private CoreTransactionService $transactionService;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var LocaleResolver
     */
    private LocaleResolver $localeResolver;

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param CoreTransactionService $transactionService
     * @param CheckoutSession $checkoutSession
     * @param LocaleResolver $localeResolver
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        CoreTransactionService $transactionService,
        CheckoutSession $checkoutSession,
        LocaleResolver $localeResolver
    ) {
        parent::__construct($context, $orderRepository);
        $this->transactionService = $transactionService;
        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Handle the checkout failure callback and redirect to the failure page.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $order = $this->getOrder();

        $this->checkoutSession->restoreQuote();

        $failureMessage = $this->transactionService->getFailureMessage(
            (int)$order->getPostfinancecheckoutSpaceId(),
            (int)$order->getPostfinancecheckoutTransactionId(),
            (string)$this->localeResolver->getLocale(),
            (string)\__('The payment process could not have been finished successfully.')
        );

        $this->messageManager->addErrorMessage($failureMessage);
        return $this->_redirect($this->getFailureRedirectionPath($order));
    }

    /**
     * Gets the path to redirect the customer to.
     *
     * @param Order $order
     * @return string
     */
    private function getFailureRedirectionPath(Order $order)
    {
        $response = new DataObject();
        $response->setPath('checkout/cart');
        $this->_eventManager->dispatch(
            'postfinancecheckout_failure_redirection_path',
            [
                'order' => $order,
                'response' => $response
            ]
        );
        return $response->getPath();
    }
}
