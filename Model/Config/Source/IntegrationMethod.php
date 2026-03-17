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
class IntegrationMethod implements \Magento\Framework\Option\ArrayInterface
{

    public const IFRAME = 'iframe';
    public const LIGHTBOX = 'lightbox';
    public const PAYMENT_PAGE = 'payment_page';

    /**
     * Return integration method options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::IFRAME,
                'label' => \__('Iframe')
            ],
            [
                'value' => self::LIGHTBOX,
                'label' => \__('Lightbox')
            ],
            [
                'value' => self::PAYMENT_PAGE,
                'label' => \__('Payment Page')
            ]
        ];
    }
}
