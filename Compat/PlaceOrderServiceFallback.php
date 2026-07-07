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
 * Stub base used when Hyvä Checkout module is not present.
 * PostFinanceCheckout\Payment\Compat\PlaceOrderServiceBase is aliased
 * to this class so that PlaceOrderService can be declared and reflected
 * during DI compilation without a fatal errors when AbstractPlaceOrderService
 * from Hyvä Checkout is not isntalled.
 */
class PlaceOrderServiceFallback
{
}
