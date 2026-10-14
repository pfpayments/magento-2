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
declare(strict_types=1);

namespace PostFinanceCheckout\Payment\Model\PluginCore;

use PostFinanceCheckout\PluginCore\Transaction\TransactionPersistenceInterface;

/**
 * Persistence strategy for {@see \PostFinanceCheckout\PluginCore\Transaction\TransactionService::upsert()}
 * calls where the order already holds its transaction id.
 *
 * If upsert() had to create a replacement transaction, the id must not be adopted: the caller
 * compares the returned id with the order's and fails the checkout instead.
 */
class NoOpTransactionPersistence implements TransactionPersistenceInterface
{
    /**
     * @param int $transactionId
     * @return void
     */
    public function persist(int $transactionId): void
    {
    }
}
