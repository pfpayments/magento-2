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
namespace PostFinanceCheckout\Payment\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use PostFinanceCheckout\Payment\Helper\Locale as LocaleHelper;
use PostFinanceCheckout\Payment\Model\Service\Order\TransactionService;
use PostFinanceCheckout\Sdk\Model\TransactionVoidState;

/**
 * Payment gateway command to void a payment.
 */
class VoidCommand implements CommandInterface
{

    /**
     *
     * @var LocaleHelper
     */
    private $localeHelper;

    /**
     *
     * @var TransactionService
     */
    private $orderTransactionService;

    /**
     *
     * @param LocaleHelper $localeHelper
     * @param TransactionService $orderTransactionService
     */
    public function __construct(LocaleHelper $localeHelper, TransactionService $orderTransactionService)
    {
        $this->localeHelper = $localeHelper;
        $this->orderTransactionService = $orderTransactionService;
    }

    public function execute(array $commandSubject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = SubjectReader::readPayment($commandSubject)->getPayment();

        $void = $this->orderTransactionService->void($payment->getOrder());
        if ($void->getState() == TransactionVoidState::FAILED) {
            throw new \Magento\Framework\Exception\LocalizedException(
                \__('The void of the payment failed on the gateway: %1',
                    $this->localeHelper->translate($void->getFailureReason()
                        ->getDescription())));
        }
    }
}