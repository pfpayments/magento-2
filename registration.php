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
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'PostFinanceCheckout_Payment',
    __DIR__
);

/**
 * Compatibility aliases for optional dependencies.
 *
 * Two of our classes extend classes from external modules which may not always
 * be installed:
 *  - GiftCardAccountWrapper extends GiftCardAccountManagement from Magento_GiftCardAccount
 *  - PlaceOrderService extends AbstractPlaceOrderService from Hyvä Checkout
 *
 * To avoid fatal errors when those classes are missing, we point a base
 * alias at either the real class or a local stub (fallback), depending on
 * what is available.
 *
 * This runs from registration.php so the aliases are defined before Magento
 * compiles the DI graph and reflects the dependent classes.
 */

if (!\class_exists(\PostFinanceCheckout\Payment\Compat\GiftCardAccountBase::class, false)) {
    \class_alias(
        \class_exists(\Magento\GiftCardAccount\Model\Service\GiftCardAccountManagement::class)
            ? \Magento\GiftCardAccount\Model\Service\GiftCardAccountManagement::class
            : \PostFinanceCheckout\Payment\Compat\GiftCardAccountFallback::class,
        \PostFinanceCheckout\Payment\Compat\GiftCardAccountBase::class
    );
}

if (!\class_exists(\PostFinanceCheckout\Payment\Compat\PlaceOrderServiceBase::class, false)) {
    \class_alias(
        \class_exists(Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService::class)
            ? Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService::class
            : \PostFinanceCheckout\Payment\Compat\PlaceOrderServiceFallback::class,
        \PostFinanceCheckout\Payment\Compat\PlaceOrderServiceBase::class
    );
}
