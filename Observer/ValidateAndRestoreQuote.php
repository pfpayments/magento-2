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
namespace PostFinanceCheckout\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\Session as CheckoutSession;
use PostFinanceCheckout\Payment\Model\Service\Order\TransactionService;
use PostFinanceCheckout\Sdk\Model\Transaction;
use PostFinanceCheckout\Sdk\Model\TransactionState;

/**
 * Observer to validate and control quote restoration.
 */
class ValidateAndRestoreQuote implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var TransactionService
     */
    private $transactionService;

    /**
     *
     * @param CheckoutSession $checkoutSession
     * @param TransactionService $transactionService
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        TransactionService $transactionService
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->transactionService = $transactionService;
    }

    /**
     * Validate and restore the quote.
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        // After placeOrder, the session's current quote is a new empty one.
        // The quote we need to reactivate is referenced by the last real order.
        $order = $this->checkoutSession->getLastRealOrder();

        // Idempotent: if there's no last order (e.g. restoreQuote already ran
        // earlier in this request and unset lastRealOrderId), there's nothing
        // to do — silently return instead of throwing.
        if (!$order || !$order->getId()) {
            return;
        }

        // Block restore only when the PostFinance Checkout transaction is in a
        // terminal paid state. Magento's order state is unreliable here: the
        // order can sit in `processing` immediately after placeOrder while
        // the PostFinance Checkout transaction is still CONFIRMED/PROCESSING (e.g.,
        // the customer is on the 3DS page).
        $spaceId = $order->getPostfinancecheckoutSpaceId();
        $transactionId = $order->getPostfinancecheckoutTransactionId();
        if ($spaceId && $transactionId) {
            try {
                $transaction = $this->transactionService->getTransaction($spaceId, $transactionId);
                if ($transaction instanceof Transaction) {
                    $paidStates = [
                        TransactionState::AUTHORIZED,
                        TransactionState::COMPLETED,
                        TransactionState::FULFILL,
                    ];
                    if (in_array($transaction->getState(), $paidStates, true)) {
                        throw new LocalizedException(
                            __('Your cart has already been paid for and cannot be restored.')
                        );
                    }
                }
            } catch (LocalizedException $e) {
                throw $e;
            } catch (\Exception $e) {
                // If the PostFinance Checkout API is unreachable, fail open and let
                // the restore proceed — better than stranding the customer.
            }
        }

        // Reactivates the quote and dispatches `restore_quote`. The abandoned
        // order stays in `pending_payment` until the PostFinance Checkout webhook
        // reports FAILED/DECLINED and FailedCommand/DeclineCommand cancels it.
        $this->checkoutSession->restoreQuote();
    }
}
