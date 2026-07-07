/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @package PostFinanceCheckout_Payment
 * @author PostFinance Ltd (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)

 */
define([], function () {
    'use strict';

    /**
     * Loads the device identifier script using Vanilla JS script injection.
     *
     * @param {Object} options
     * @param {string} identifier
     */
    function loadScript(options, identifier) {
        if (options.scriptUrl && identifier) {
            var url = options.scriptUrl + identifier;
            var existingScript = document.querySelector('script[src="' + url + '"]');
            if (existingScript) {
                return;
            }
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = url;
            script.async = true;
            document.head.appendChild(script);
        }
    }

    /**
     * Initializes the device identifier process via native fetch.
     *
     * @param {Object} options
     */
    return function (options) {
        fetch(options.identifierUrl)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(function (sessionIdentifier) {
                loadScript(options, sessionIdentifier);
            })
            .catch(function (error) {
                console.error('There was a problem with the fetch operation:', error);
            });
    };
});