<?php

declare(strict_types=1);

namespace PostFinanceCheckout\Payment\Model\CoreWebhook\PaymentMethodConfiguration;

use PostFinanceCheckout\Payment\Api\PaymentMethodConfigurationManagementInterface;
use PostFinanceCheckout\PluginCore\Log\LoggerInterface;
use PostFinanceCheckout\PluginCore\Webhook\Command\WebhookCommand;
use PostFinanceCheckout\PluginCore\Webhook\WebhookContext;

class SynchronizeCommand extends WebhookCommand
{

    /**
     *
     * @param WebhookContext $context
     * @param LoggerInterface $logger
     * @param PaymentMethodConfigurationManagementInterface $management
     */
    public function __construct(
        WebhookContext $context,
        LoggerInterface $logger,
        private readonly PaymentMethodConfigurationManagementInterface $management
    ) {
        parent::__construct($context, $logger);
    }

    /**
     * Execute synchronize command for the current webhook context.
     *
     * @return mixed
     */
    public function execute(): mixed
    {
        $this->logger->info('Running SynchronizeCommand');

        $this->management->synchronize();

        $this->logger->debug('Command Synchronize for PaymentMethodConfiguration completed.');

        return null;
    }
}
