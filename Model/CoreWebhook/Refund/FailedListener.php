<?php

declare(strict_types=1);

namespace PostFinanceCheckout\Payment\Model\CoreWebhook\Refund;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PostFinanceCheckout\Payment\Api\TransactionInfoRepositoryInterface;
use PostFinanceCheckout\PluginCore\Log\LoggerInterface;
use PostFinanceCheckout\PluginCore\Refund\RefundGatewayInterface;
use PostFinanceCheckout\PluginCore\Webhook\Command\WebhookCommandInterface;
use PostFinanceCheckout\PluginCore\Webhook\Listener\WebhookListenerInterface;
use PostFinanceCheckout\PluginCore\Webhook\WebhookContext;

class FailedListener implements WebhookListenerInterface
{
    /**
     *
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionInfoRepositoryInterface $transactionInfoRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RefundGatewayInterface $pluginCoreRefundGateway
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly TransactionInfoRepositoryInterface $transactionInfoRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly RefundGatewayInterface $pluginCoreRefundGateway,
    ) {
    }

    /**
     * Create webhook command for the given context.
     *
     * @param WebhookContext $context
     * @return WebhookCommandInterface
     */
    public function getCommand(WebhookContext $context): WebhookCommandInterface
    {
        return new FailedCommand(
            $context,
            $this->logger,
            $this->orderRepository,
            $this->transactionInfoRepository,
            $this->searchCriteriaBuilder,
            $this->pluginCoreRefundGateway
        );
    }
}
