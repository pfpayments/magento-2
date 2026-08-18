<?php

declare(strict_types=1);

namespace PostFinanceCheckout\Payment\Model\Settings;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use PostFinanceCheckout\PluginCore\Sdk\ClientMetadata;
use PostFinanceCheckout\PluginCore\Sdk\ClientMetadataProviderInterface;

/**
 * Identifies this Magento installation and plugin version to the portal API.
 */
class ClientMetadataProvider implements ClientMetadataProviderInterface
{
    /**
     * @var string
     */
    private const XML_PATH_PLUGIN_VERSION = 'postfinancecheckout_payment/information/version';

    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ProductMetadataInterface $productMetadata,
    ) {
    }

    /**
     * Returns the metadata identifying this Magento installation and plugin version.
     *
     * @return ClientMetadata|null
     */
    public function getClientMetadata(): ?ClientMetadata
    {
        return new ClientMetadata(
            shopSystem: 'magento',
            shopSystemVersion: $this->productMetadata->getVersion(),
            pluginVersion: '3.4.0',
        );
    }
}
