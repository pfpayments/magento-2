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
namespace PostFinanceCheckout\Payment\Api;

use PostFinanceCheckout\Payment\Api\Data\TokenInfoInterface;

/**
 * Token info management interface.
 *
 * @api
 */
interface TokenInfoManagementInterface
{

    /**
     * Fetches the token version's latest state from PostFinance Checkout and updates the stored information.
     *
     * @param int $spaceId
     * @param int $tokenVersionId
     * @return void
     */
    public function updateTokenVersion($spaceId, $tokenVersionId);

    /**
     * Fetches the token's latest state from PostFinance Checkout and updates the stored information.
     *
     * @param int $spaceId
     * @param int $tokenId
     * @return void
     */
    public function updateToken($spaceId, $tokenId);

    /**
     * Deletes the token on PostFinance Checkout.
     *
     * @param TokenInfoInterface $token
     * @return void
     */
    public function deleteToken(TokenInfoInterface $token);
}