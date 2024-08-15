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
namespace PostFinanceCheckout\Payment\Plugin\Sales\Model\AdminOrder;

use PostFinanceCheckout\Payment\Model\Payment\Method\Adapter;

class Create
{

    /**
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @return void
     */
    public function beforeCreateOrder(\Magento\Sales\Model\AdminOrder\Create $subject)
    {
        if ($subject->getQuote()
            ->getPayment()
            ->getMethodInstance() instanceof Adapter) {
            $subject->setSendConfirmation(false);
        }
    }
}