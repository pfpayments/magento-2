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
namespace PostFinanceCheckout\Payment\Plugin\Payment\Method;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Prevents the vendor PostFinance Adapter from calling getPossiblePaymentMethods()
 * (and thereby updateTransactionByQuote) against an already-placed order's quote.
 * In Hyvä checkout, Magewire re-renders the payment method list after placeOrder(),
 * triggering isAvailable() while the quote is already inactive.
 */
class MethodAdapter
{
    /**
     * Skips availability check for inactive quotes to prevent redundant transaction updates.
     *
     * @param \PostFinanceCheckout\Payment\Model\Payment\Method\Adapter $subject
     * @param callable $proceed
     * @param CartInterface|null $quote
     * @return bool
     */
    public function aroundIsAvailable(
        \PostFinanceCheckout\Payment\Model\Payment\Method\Adapter $subject,
        callable $proceed,
        ?CartInterface $quote = null
    ): bool {
        if ($quote !== null && !$quote->getIsActive()) {
            return false;
        }
        return $proceed($quote);
    }
}
