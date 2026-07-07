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
namespace PostFinanceCheckout\Payment\Compat;

/**
 * Stub base used when Magento_GiftCardAccount module is not present.
 * PostFinanceCheckout\Payment\Compat\GiftCardAccountBase is aliased
 * to this class so that GiftCardAccountWrapper can be declared and reflected
 * during DI compilation without fatal errors when GiftCardAccountManagement
 * from Magento_GiftCardAccount is not isntalled.
 */
class GiftCardAccountFallback
{
}
