/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @package PostFinanceCheckout_Payment
 * @author wallee AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)

 */
require([
    'jquery',
], function ($) {
	$(function () {
		$('.postfinancecheckout-label-container').each(function(){
			var container = $(this),
			
				toggleTable = function(){
					container.toggleClass('active');
				};
			
			container.find('> a').on('click', toggleTable);
		});
	});
});