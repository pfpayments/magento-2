<?php
/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @package PostFinanceCheckout_Payment
 * @author wallee AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)

 */
namespace PostFinanceCheckout\Payment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Psr\Log\LoggerInterface;

/**
 * Service to provide PostFinance Checkout API client.
 */
class ApiClient
{

    /**
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     *
     * @var EncryptorInterface
     */
    private $encrypter;

    /**
     *
     * @var \PostFinanceCheckout\Sdk\ApiClient
     */
    private $apiClient;

    /**
     * List of shared service instances
     *
     * @var array<mixed>
     */
    private $sharedInstances = [];

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encrypter
     * @param LoggerInterface $logger
     */
    public function __construct(ScopeConfigInterface $scopeConfig, EncryptorInterface $encrypter,  LoggerInterface $logger)
    {
        $this->scopeConfig = $scopeConfig;
        $this->encrypter = $encrypter;
        $this->logger = $logger;
    }

    /**
     * Retrieve cached service instance.
     *
     * @param string $type
     * @return mixed
     * @throws ApiClientException
     */
    public function getService($type)
    {
        $this->logger->debug("API-CLIENT::getService ".$type);
        $type = \ltrim($type, '\\');
        if (! isset($this->sharedInstances[$type])) {
            $this->sharedInstances[$type] = new $type($this->getApiClient());
        }
        return $this->sharedInstances[$type];
    }

    /**
     * Gets the gateway API client.
     *
     * @throws \PostFinanceCheckout\Payment\Model\ApiClientException
     * @return \PostFinanceCheckout\Sdk\ApiClient
     */
    public function getApiClient()
    {
        if ($this->apiClient == null) {
            $userId = $this->scopeConfig->getValue('postfinancecheckout_payment/general/api_user_id');
            $applicationKey = $this->scopeConfig->getValue('postfinancecheckout_payment/general/api_user_secret');
            if (! empty($userId) && ! empty($applicationKey)) {
                $client = new \PostFinanceCheckout\Sdk\ApiClient($userId, $this->encrypter->decrypt($applicationKey));
                $client->setBasePath($this->getBaseGatewayUrl() . '/api');
                $this->apiClient = $client;
                $apiClientHeaders = new ApiClientHeaders();
                $apiClientHeaders->addHeaders($this->apiClient);
            } else {
                throw new \PostFinanceCheckout\Payment\Model\ApiClientException(
                    'The PostFinance Checkout API user data are incomplete.');
            }
        }
        return $this->apiClient;
    }

    /**
     * Gets whether the required data to connect to the gateway are provided.
     *
     * @return boolean
     */
    public function checkApiClientData()
    {
        $userId = $this->scopeConfig->getValue('postfinancecheckout_payment/general/api_user_id');
        $applicationKey = $this->scopeConfig->getValue('postfinancecheckout_payment/general/api_user_secret');
        if (! empty($userId) && ! empty($applicationKey)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets the base URL to the gateway.
     *
     * @return string
     */
    protected function getBaseGatewayUrl()
    {
        return \rtrim($this->scopeConfig->getValue('postfinancecheckout_payment/general/base_gateway_url'), '/');
    }
}