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
namespace PostFinanceCheckout\Payment\Gateway\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use PostFinanceCheckout\PluginCore\Log\LoggerInterface;
use PostFinanceCheckout\PluginCore\Transaction\Completion\TransactionCompletionService;
use PostFinanceCheckout\PluginCore\Transaction\Exception\TransactionException;
use PostFinanceCheckout\PluginCore\Transaction\Completion\State as CoreState;

/**
 * Payment gateway command to void a payment.
 */
class VoidCommand implements CommandInterface
{

    /**
     *
     * @param TransactionCompletionService $completionService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly TransactionCompletionService $completionService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Void the order transaction for the given payment command.
     *
     * @param array $commandSubject
     * @return void
     * @throws LocalizedException
     */
    public function execute(array $commandSubject): void
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = SubjectReader::readPayment($commandSubject)->getPayment();
        $order = $payment->getOrder();

        try {
            $completion = $this->completionService->void(
                (int) $order->getPostfinancecheckoutSpaceId(),
                (int) $order->getPostfinancecheckoutTransactionId()
            );
        } catch (TransactionException $e) {
            $this->logger->error('Void failed on the gateway.', [
                'orderId' => $order->getIncrementId(),
                'exception' => $e,
            ]);
            throw new LocalizedException(
                \__('The void of the payment failed on the gateway: %1', $e->getMessage())
            );
        }

        if ($completion->state === CoreState::FAILED) {
            $this->logger->error('Void was rejected by the gateway.', [
                'orderId' => $order->getIncrementId(),
            ]);
            throw new \Magento\Framework\Exception\LocalizedException(
                \__('The capture of the invoice failed on the gateway.')
            );
        }

        $this->logger->info('Void completed.', ['orderId' => $order->getIncrementId()]);
    }
}
