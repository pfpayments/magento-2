<?php

declare(strict_types=1);

namespace PostFinanceCheckout\Payment\Model\CoreWebhook\ManualTask;

use PostFinanceCheckout\Payment\Model\Service\ManualTaskService;
use PostFinanceCheckout\PluginCore\Log\LoggerInterface;
use PostFinanceCheckout\PluginCore\Webhook\Command\WebhookCommand;
use PostFinanceCheckout\PluginCore\Webhook\WebhookContext;

class UpdateCommand extends WebhookCommand
{

    /**
     *
     * @param LoggerInterface $logger
     * @param WebhookContext $context
     * @param ManualTaskService $manualTaskService
     */
    public function __construct(
        LoggerInterface $logger,
        WebhookContext $context,
        private readonly ManualTaskService $manualTaskService
    ) {
        parent::__construct($context, $logger);
    }

    /**
     * Execute update command for the current webhook context.
     *
     * @return mixed
     */
    public function execute(): mixed
    {
        $this->logger->info('Running UpdateCommand');

        $this->manualTaskService->update();

        $this->logger->debug('Command Update for ManualTask completed.');

        return null;
    }
}
