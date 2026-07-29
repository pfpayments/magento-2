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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use PostFinanceCheckout\PluginCore\Log\LoggerInterface;
use PostFinanceCheckout\Payment\Api\RefundJobRepositoryInterface;
use PostFinanceCheckout\Payment\Model\Service\RefundService;
use PostFinanceCheckout\PluginCore\Refund\Exception\InvalidRefundException;
use PostFinanceCheckout\PluginCore\Refund\Exception\RefundException;
use PostFinanceCheckout\PluginCore\Refund\RefundService as CoreRefundService;
use PostFinanceCheckout\PluginCore\Refund\State as CoreState;
use PostFinanceCheckout\PluginCore\Transaction\Exception\TransactionException;

/**
 * Payment gateway command to refund a payment.
 */
class RefundCommand implements CommandInterface
{

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var RefundJobRepositoryInterface
     */
    private $refundJobRepository;

    /**
     *
     * @var RefundService
     */
    private $refundService;

    /**
     *
     * @var CoreRefundService
     */
    private $pluginCoreRefundService;

    /**
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     *
     * @param LoggerInterface $logger
     * @param RefundJobRepositoryInterface $refundJobRepository
     * @param RefundService $refundService
     * @param CoreRefundService $pluginCoreRefundService
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        LoggerInterface $logger,
        RefundJobRepositoryInterface $refundJobRepository,
        RefundService $refundService,
        CoreRefundService $pluginCoreRefundService,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->refundJobRepository = $refundJobRepository;
        $this->refundService = $refundService;
        $this->pluginCoreRefundService = $pluginCoreRefundService;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Execute refund command for the given payment subject.
     *
     * @param array $commandSubject
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = SubjectReader::readPayment($commandSubject)->getPayment();
        $creditmemo = $payment->getCreditmemo();
        $isIgnorePendingRefundStatusEnabled = $this->scopeConfig->getValue(
            'postfinancecheckout_payment/pending_refund_status/pending_refund_status_enabled'
        );

        if ($creditmemo->getData('postfinancecheckout_external_id') == null) {
            $spaceId = (int) $creditmemo->getOrder()->getPostfinancecheckoutSpaceId();

            try {
                $refundJob = $this->refundJobRepository->getByOrderId($payment->getOrder()
                    ->getId());
            } catch (NoSuchEntityException $e) {
                $context = $this->refundService->createRefund($creditmemo);
                $refundJob = $this->refundService->createRefundJob($creditmemo->getInvoice(), $context);
            }

            try {
                $refund = $this->pluginCoreRefundService->createRefund($spaceId, $refundJob->getRefund());
            } catch (InvalidRefundException|RefundException|TransactionException $e) {
                if ($e->isRetryable()) {
                    // Transient failure: keep the job for the CRON to retry.
                    $creditmemo->setData('postfinancecheckout_keep_refund_job', true);
                    $this->logger->critical('Refund request failed; keeping the job for a retry.', [
                        'refundJobId' => $refundJob->getId(),
                        'orderId' => $creditmemo->getOrder()->getIncrementId(),
                        'exception' => $e,
                    ]);
                } else {
                    // Terminal failure: leave the job to be deleted by CreditmemoService::aroundRefund().
                    $this->logger->critical('Refund rejected by the gateway.', [
                        'refundJobId' => $refundJob->getId(),
                        'orderId' => $creditmemo->getOrder()->getIncrementId(),
                        'exception' => $e,
                    ]);
                }
                throw new \Magento\Framework\Exception\LocalizedException(
                    \__($e->getLocalizedMessage()->getDefault())
                );
            } catch (\Exception $e) {
                $creditmemo->setData('postfinancecheckout_keep_refund_job', true);
                $this->logger->critical('Unexpected error while sending the refund to the gateway.', [
                    'refundJobId' => $refundJob->getId(),
                    'orderId' => $creditmemo->getOrder()->getIncrementId(),
                    'exception' => $e,
                ]);
                throw new \Magento\Framework\Exception\LocalizedException(
                    \__('There has been an error while sending the refund to the gateway.')
                );
            }

            if ($refund->state == CoreState::FAILED) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    \__($refund->failureReason?->getDefault()
                    ?? 'The refund could not be processed on the gateway.')
                );
            } elseif (! $isIgnorePendingRefundStatusEnabled &&
                ( $refund->state == CoreState::PENDING ||
                $refund->state == CoreState::MANUAL_CHECK )) {
                $creditmemo->setData('postfinancecheckout_keep_refund_job', true);
                throw new \Magento\Framework\Exception\LocalizedException(
                    \__('The refund was requested successfully, but is still pending on the gateway.')
                );
            }

            $creditmemo->setData('postfinancecheckout_external_id', $refund->externalId);
            $this->refundJobRepository->delete($refundJob);
        }
    }
}
