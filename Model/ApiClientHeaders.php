<?php
/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html/).
 *
 * @package PostFinanceCheckout_Payment
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
namespace PostFinanceCheckout\Payment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use PostFinanceCheckout\Sdk\ApiClient;

/**
 * Service to provide PostFinance Checkout API client.
 */
class ApiClientHeaders
{

    /**
     * @var SHOP_SYSTEM
     */
    public const SHOP_SYSTEM = 'x-meta-shop-system';
	
    /**
     * @var SHOP_SYSTEM_VERSION
     */
    public const SHOP_SYSTEM_VERSION = 'x-meta-shop-system-version';
	
    /**
     * @var SHOP_SYSTEM_AND_VERSION
     */
    public const SHOP_SYSTEM_AND_VERSION = 'x-meta-shop-system-and-version';

    /**
     * Sets the headers.
     *
     * @param \PostFinanceCheckout\Sdk\ApiClient $apiClient
     */
    public function addHeaders(ApiClient &$apiClient)
    {
        $data = self::getDefaultData();
		foreach ($data as $key => $value) {
			$apiClient->addDefaultHeader($key, $value);
		}
    }

    /**
	 * @return array
	 */
	protected static function getDefaultData()
	{

        // todo refactor using DI: https://www.rohanhapani.com/how-to-find-out-version-of-magento-2-programmatically/;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
        $productMetadata = $objectManager->get('\Magento\Framework\App\ProductMetadataInterface'); 
        $shop_version = $productMetadata->getVersion();

		[$major_version, $minor_version, $rest] = explode('.', $shop_version, 3);
		return [
			self::SHOP_SYSTEM             => 'magento',
			self::SHOP_SYSTEM_VERSION     => $shop_version,
			self::SHOP_SYSTEM_AND_VERSION => 'magento-' . $major_version . '.' . $minor_version,
		];
	}
}