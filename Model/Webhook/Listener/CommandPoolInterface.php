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
namespace PostFinanceCheckout\Payment\Model\Webhook\Listener;

/**
 * Webhook listener command pool interface.
 */
interface CommandPoolInterface
{

    /**
     * Retrieves listener.
     *
     * @param string $commandCode
     * @return CommandInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function get($commandCode);
}