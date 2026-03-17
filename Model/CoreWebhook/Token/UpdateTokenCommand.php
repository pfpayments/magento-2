<?php

declare(strict_types=1);

namespace PostFinanceCheckout\Payment\Model\CoreWebhook\Token;

use PostFinanceCheckout\Payment\Api\TokenInfoManagementInterface;
use PostFinanceCheckout\PluginCore\Log\LoggerInterface;
use PostFinanceCheckout\PluginCore\Webhook\Command\WebhookCommand;
use PostFinanceCheckout\PluginCore\Webhook\WebhookContext;

class UpdateTokenCommand extends WebhookCommand
{
    /**
     *
     * @param WebhookContext $context
     * @param LoggerInterface $logger
     * @param TokenInfoManagementInterface $tokenInfoManagement
     */
    public function __construct(
        WebhookContext $context,
        LoggerInterface $logger,
        private readonly TokenInfoManagementInterface $tokenInfoManagement,
    ) {
        parent::__construct($context, $logger);
    }

    /**
     * Execute update token command for the current webhook context.
     *
     * @return mixed
     */
    public function execute(): mixed
    {
        $this->logger->info(
            sprintf(
                'Running UpdateTokenCommand for entity ID: %d',
                $this->context->entityId
            )
        );

        $spaceId = $this->context->spaceId;
        $tokenId = $this->context->entityId;

        $this->tokenInfoManagement->updateToken($spaceId, $tokenId);

        $this->logger->debug(
            sprintf(
                'Command UpdateToken for entity Token/%d completed.',
                $this->context->entityId
            )
        );
        return null;
    }
}
