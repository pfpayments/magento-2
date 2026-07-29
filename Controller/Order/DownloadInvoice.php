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
namespace PostFinanceCheckout\Payment\Controller\Order;

use PostFinanceCheckout\Payment\Api\Data\TransactionInfoInterface;
use PostFinanceCheckout\Payment\Controller\Order\AbstractDownloadDocument;
use PostFinanceCheckout\PluginCore\Document\RenderedDocument;

/**
 * Frontend controller action to download an invoice document.
 */
class DownloadInvoice extends AbstractDownloadDocument
{

    /**
     * @inheritDoc
     */
    protected function isDocumentDownloadAllowed(TransactionInfoInterface $transaction, $storeId): bool
    {
        return $this->documentHelper->isInvoiceDownloadAllowed($transaction, $storeId);
    }

    /**
     * @inheritDoc
     */
    protected function getDocument(int $spaceId, int $transactionId): RenderedDocument
    {
        return $this->documentService->getInvoice($spaceId, $transactionId);
    }
}
