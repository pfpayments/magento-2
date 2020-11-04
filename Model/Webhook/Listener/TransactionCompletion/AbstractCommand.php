<?php
/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout/).
 *
 * @package PostFinanceCheckout_Payment
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
namespace PostFinanceCheckout\Payment\Model\Webhook\Listener\TransactionCompletion;

use PostFinanceCheckout\Payment\Model\Webhook\Listener\AbstractOrderRelatedCommand;

/**
 * Abstract webhook listener command to handle transaction completions.
 */
abstract class AbstractCommand extends AbstractOrderRelatedCommand
{
}