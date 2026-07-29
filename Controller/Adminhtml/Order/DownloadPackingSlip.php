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
namespace PostFinanceCheckout\Payment\Controller\Adminhtml\Order;

use PostFinanceCheckout\PluginCore\Document\RenderedDocument;

/**
 * Backend controller action to download a packing slip.
 */
class DownloadPackingSlip extends AbstractDownloadDocument
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @inheritDoc
     */
    protected function getDocument(int $spaceId, int $transactionId): RenderedDocument
    {
        return $this->documentService->getPackingSlip($spaceId, $transactionId);
    }
}
