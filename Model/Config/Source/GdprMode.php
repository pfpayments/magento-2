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
namespace PostFinanceCheckout\Payment\Model\Config\Source;

/**
 * Provides the integration methods as array options.
 */
class GdprMode implements \Magento\Framework\Option\ArrayInterface
{

    const ENABLED = 'enabled';
    const DISABLED = 'disabled';

    public function toOptionArray()
    {
        return [
            [
                'value' => self::ENABLED,
                'label' => \__('Enabled')
            ],
            [
                'value' => self::DISABLED,
                'label' => \__('Disabled')
            ]
        ];
    }
}