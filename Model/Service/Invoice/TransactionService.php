<?php
/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @package PostFinanceCheckout_Payment
 * @author PostFinance Ltd (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)

 */
namespace PostFinanceCheckout\Payment\Model\Service\Invoice;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use PostFinanceCheckout\Payment\Model\Service\AbstractTransactionService;
use PostFinanceCheckout\Payment\Model\Service\Order\TransactionService as OrderTransactionService;
use PostFinanceCheckout\PluginCore\LineItem\LineItemCollection;
use PostFinanceCheckout\PluginCore\Transaction\Invoice\Invoice as CoreInvoice;
use PostFinanceCheckout\PluginCore\Transaction\Invoice\State as CoreTransactionInvoiceState;
use PostFinanceCheckout\PluginCore\Log\LoggerInterface;
use PostFinanceCheckout\PluginCore\Transaction\Completion\CaptureRequest;
use PostFinanceCheckout\PluginCore\Transaction\Completion\Exception\CompletionException;
use PostFinanceCheckout\PluginCore\Transaction\Completion\State as CoreState;
use PostFinanceCheckout\PluginCore\Transaction\Completion\TransactionCompletionGatewayInterface;

/**
 * Service to handle transactions in invoice context.
 */
class TransactionService extends AbstractTransactionService
{

    /**
     *
     * @var LineItemService
     */
    private $lineItemService;

    /**
     *
     * @var OrderTransactionService
     */
    private $orderTransactionService;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var TransactionCompletionGatewayInterface
     */
    private TransactionCompletionGatewayInterface $completionGateway;

    /**
     *
     * @param CustomerRegistry $customerRegistry
     * @param CookieManagerInterface $cookieManager
     * @param LineItemService $lineItemService
     * @param OrderTransactionService $orderTransactionService
     * @param LoggerInterface $logger
     * @param TransactionCompletionGatewayInterface $completionGateway
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        CookieManagerInterface $cookieManager,
        LineItemService $lineItemService,
        OrderTransactionService $orderTransactionService,
        LoggerInterface $logger,
        TransactionCompletionGatewayInterface $completionGateway,
    ) {
        parent::__construct(
            $customerRegistry,
            $cookieManager
        );
        $this->lineItemService = $lineItemService;
        $this->orderTransactionService = $orderTransactionService;
        $this->logger = $logger;
        $this->completionGateway = $completionGateway;
    }

    /**
     * Completes the transaction linked to the given payment's and invoice's order.
     *
     * Sends this invoice's line items to the gateway as part of the capture request
     * itself — the gateway's unified capture API declares and captures atomically,
     * so there is no separate "declare line items" step anymore.
     *
     * @param Payment $payment
     * @param Invoice $invoice
     * @param float $amount
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function complete(Payment $payment, Invoice $invoice, float $amount): void
    {
        $order = $invoice->getOrder();
        $lineItems = $this->lineItemService->convertInvoiceLineItems($invoice, $amount);
        $captureRequest = new CaptureRequest(
            lineItems: new LineItemCollection(...$lineItems),
            isFinal: ! $order->canInvoice(),
            externalId: uniqid(),
            merchantReference: $invoice->getIncrementId(),
        );

        try {
            $completion = $this->completionGateway->capture(
                (int) $order->getPostfinancecheckoutSpaceId(),
                (int) $order->getPostfinancecheckoutTransactionId(),
                $captureRequest
            );
        } catch (CompletionException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                \__('The capture of the invoice failed on the gateway: %1', $e->getMessage())
            );
        }

        if ($completion->state === CoreState::FAILED) {
            throw new \Magento\Framework\Exception\LocalizedException(
                \__('The capture of the invoice failed on the gateway.')
            );
        }

        try {
            $transactionInvoice = $this->orderTransactionService->getTransactionInvoice($invoice->getOrder());
            if ($transactionInvoice instanceof CoreInvoice &&
                $transactionInvoice->state !== CoreTransactionInvoiceState::PAID &&
                $transactionInvoice->state !== CoreTransactionInvoiceState::NOT_APPLICABLE) {
                $invoice->setPostfinancecheckoutCapturePending(true);
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->debug(
                sprintf(
                    "There was an issue completing the %s transaction.",
                    $payment->getTransactionId(),
                ),
                ['exception' => $e]
            );
        }

        $authorizationTransaction = $payment->getAuthorizationTransaction();
        if ($authorizationTransaction) {
            $authorizationTransaction->close(false);
            $invoice->getOrder()
                ->addRelatedObject($invoice)
                ->addRelatedObject($authorizationTransaction);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                \__(
                    'The capture of the invoice failed in the store: %1.',
                    \__('The associated authorization transaction for the payment could not be found.')
                )
            );
        }
    }
}
