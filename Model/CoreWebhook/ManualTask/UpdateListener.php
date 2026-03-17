<?php

declare(strict_types=1);

namespace PostFinanceCheckout\Payment\Model\CoreWebhook\ManualTask;

use PostFinanceCheckout\Payment\Model\Service\ManualTaskService;
use PostFinanceCheckout\PluginCore\Webhook\Command\WebhookCommandInterface;
use PostFinanceCheckout\PluginCore\Webhook\Listener\WebhookListenerInterface;
use PostFinanceCheckout\PluginCore\Webhook\WebhookContext;
use PostFinanceCheckout\PluginCore\Log\LoggerInterface;

class UpdateListener implements WebhookListenerInterface
{

    /**
     *
     * @param LoggerInterface $logger
     * @param ManualTaskService $manualTaskService
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ManualTaskService $manualTaskService,
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
        return new UpdateCommand($this->logger, $context, $this->manualTaskService);
    }
}
