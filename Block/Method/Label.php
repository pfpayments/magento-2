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
namespace PostFinanceCheckout\Payment\Block\Method;

use PostFinanceCheckout\Sdk\Model\LabelDescriptor;

/**
 * Holds the information about a label that are needed to render the label in the backend.
 */
class Label
{

    /**
     *
     * @var LabelDescriptor
     */
    private $descriptor;

    /**
     *
     * @var string
     */
    private $value;

    /**
     *
     * @param LabelDescriptor $descriptor
     * @param string $value
     */
    public function __construct(LabelDescriptor $descriptor, $value)
    {
        $this->descriptor = $descriptor;
        $this->value = $value;
    }

    /**
     * Gets the label descriptor's ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->descriptor->getId();
    }

    /**
     * Gets the label descriptor's name.
     *
     * @return array
     */
    public function getName()
    {
        return $this->descriptor->getName();
    }

    /**
     * Gets the label descriptor's weight.
     *
     * @return int
     */
    public function getWeight()
    {
        return $this->descriptor->getWeight();
    }

    /**
     * Gets the label's value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}