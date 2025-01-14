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
namespace PostFinanceCheckout\Payment\Controller\Checkout;

use Magento\Framework\DataObject;
use Magento\Framework\App\Action\Context;

/**
 * Frontend controller action to handle checkout failures.
 */
class RestoreCart extends \PostFinanceCheckout\Payment\Controller\Checkout
{

    public function execute()
    {
        try {
            // Triggers event to validate and restore quote.
            $this->_eventManager->dispatch('postfinancecheckout_validate_and_restore_quote');
        } catch (\Exception $e) {
            // If an error occurs, we display a generic message and redirect to the cart.
            $this->messageManager->addErrorMessage(__('An error occurred while restoring your cart.'));
            return $this->_redirect('checkout/cart');
        }

        // Redirects to the cart or to the path determined by the redirection.
        return $this->_redirect($this->getFailureRedirectionPath());
    }

    /**
     * Gets the path to redirect the customer to.
     *
     * @return string
     */
    private function getFailureRedirectionPath()
    {
        $response = new DataObject();
        $response->setPath('checkout/cart');
        $this->_eventManager->dispatch('postfinancecheckout_checkout_failure_redirection_path',
            [
                'response' => $response
            ]);
        return $response->getPath();
    }

}