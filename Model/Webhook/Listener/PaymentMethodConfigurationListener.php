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

use PostFinanceCheckout\Payment\Api\PaymentMethodConfigurationManagementInterface;
use PostFinanceCheckout\Payment\Model\Webhook\ListenerInterface;
use PostFinanceCheckout\Payment\Model\Webhook\Request;

/**
 * Webhook listener to handle payment method configurations.
 */
class PaymentMethodConfigurationListener implements ListenerInterface
{

    /**
     *
     * @var PaymentMethodConfigurationManagementInterface
     */
    private $paymentMethodConfigurationManagement;

    /**
     *
     * @param PaymentMethodConfigurationManagementInterface $paymentMethodConfigurationManagement
     */
    public function __construct(PaymentMethodConfigurationManagementInterface $paymentMethodConfigurationManagement)
    {
        $this->paymentMethodConfigurationManagement = $paymentMethodConfigurationManagement;
    }

    public function execute(Request $request)
    {
        $this->paymentMethodConfigurationManagement->synchronize();
    }
}