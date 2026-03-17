<?php

declare(strict_types=1);

namespace PostFinanceCheckout\Payment\Model\CoreWebhook;

use PostFinanceCheckout\PluginCore\Webhook\DefaultStateFetcher;
use PostFinanceCheckout\Sdk\Service\WebhookEncryptionService;
use PostFinanceCheckout\Sdk\Service\TransactionService;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Magento-specific wrapper for the DefaultStateFetcher.
 * Its only job is to get the spaceId from Magento's configuration.
 */
class StateFetcher extends DefaultStateFetcher
{

}
