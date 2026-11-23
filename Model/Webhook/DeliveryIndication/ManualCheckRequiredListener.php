<?php

declare(strict_types=1);

namespace PostFinanceCheckout\Payment\Model\Webhook\DeliveryIndication;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use PostFinanceCheckout\Payment\Api\TransactionInfoRepositoryInterface;
use PostFinanceCheckout\PluginCore\DeliveryIndication\DeliveryIndicationService;
use PostFinanceCheckout\PluginCore\Log\LoggerInterface;
use PostFinanceCheckout\PluginCore\Webhook\WebhookContext;
use PostFinanceCheckout\PluginCore\Webhook\Command\WebhookCommandInterface;
use PostFinanceCheckout\PluginCore\Webhook\Listener\WebhookListenerInterface;
use Magento\Sales\Model\ResourceModel\Order as OrderResourceModel;

class ManualCheckRequiredListener implements WebhookListenerInterface
{

    /**
     *
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionInfoRepositoryInterface $transactionInfoRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DeliveryIndicationService $deliveryIndicationService
     * @param OrderResourceModel $orderResourceModel
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly TransactionInfoRepositoryInterface $transactionInfoRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly DeliveryIndicationService $deliveryIndicationService,
        private readonly OrderResourceModel $orderResourceModel,
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
        return new ManualCheckRequiredCommand(
            $context,
            $this->logger,
            $this->orderRepository,
            $this->transactionInfoRepository,
            $this->searchCriteriaBuilder,
            $this->deliveryIndicationService,
            $this->orderResourceModel,
        );
    }
}
