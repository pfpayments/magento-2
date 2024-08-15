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
namespace PostFinanceCheckout\Payment\Block\Adminhtml\Sales\Order\Invoice;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Invoice;

/**
 * Block to inform about a pending capture on the backend invoice view.
 */
class View extends Template
{

    /**
     *
     * @var Registry
     */
    private $coreRegistry;

    /**
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(Context $context, Registry $coreRegistry, array $data = [])
    {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Gets whether the invoice is in a pending capture state.
     *
     * @return boolean
     */
    public function isCapturePending()
    {
        return $this->getInvoice()->getState() != Invoice::STATE_PAID &&
            $this->getInvoice()->getPostfinancecheckoutCapturePending();
    }

    /**
     * Gets the invoice model instance.
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->coreRegistry->registry('current_invoice');
    }
}