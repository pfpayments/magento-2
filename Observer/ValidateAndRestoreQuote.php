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
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session as CheckoutSession;
use PostFinanceCheckout\Sdk\Model\TransactionState;

/**
 * Observer to validate and control quote restoration.
 */
class ValidateAndRestoreQuote implements ObserverInterface
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        Order $order,
        CheckoutSession $checkoutSession
    ) {
        $this->order = $order;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Validate and restore the quote.
     *
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();

        if (!$quote || !$quote->getId()) {
            throw new LocalizedException(__('No cart to restore.'));
        }

        // Find any orders associated with the quote
        $orderCollection = $this->order->getCollection()
            ->addFieldToFilter('quote_id', $quote->getId());

        if ($orderCollection->getSize()) {
            /** @var Order $order */
            $order = $orderCollection->getFirstItem();

            $orderStates = [
                TransactionState::AUTHORIZED,
                TransactionState::COMPLETED,
                TransactionState::FULFILL,
            ];

            // Prevent restoring if the quote is already paid
            if (in_array($order->getState(), $orderStates)) {
                throw new LocalizedException(__('Your cart has already been paid for and cannot be restored.'));
            }
        }

        // If all validations pass, let the session restore the quote
        $this->checkoutSession->restoreQuote();
    }
}
