/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @package PostFinanceCheckout_Payment
 * @author wallee AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)

 */
define([
	'underscore',
	'Magento_Checkout/js/model/quote',
	'Magento_Checkout/js/action/set-shipping-information',
	'PostFinanceCheckout_Payment/js/model/default-checkout'
], function(
	_,
	quote,
	setShippingInformationAction,
	defaultCheckoutAdapter
){
	'use strict';
	return function(formId, isActive, loadPaymentForm){
		var billingAddressCache = {},
			shippingAddressCache = {},
			hasAddressChanged = false,
			addressTimeout,
			pluginCheckoutAdapter;
		
		function getCheckoutAdapter(){
			if (pluginCheckoutAdapter) {
				return pluginCheckoutAdapter;
			} else {
				return defaultCheckoutAdapter;
			}
		}
		
		function canReplacePrimaryAction() {
			return getCheckoutAdapter().canReplacePrimaryAction();
		}
		
		function isPrimaryActionReplaced() {
			return getCheckoutAdapter().isPrimaryActionReplaced();
		}
		
		function replacePrimaryAction(label) {
			getCheckoutAdapter().replacePrimaryAction(label);
		}
		
		function resetPrimaryAction() {
			getCheckoutAdapter().resetPrimaryAction();
		}
		
		function selectPaymentMethod () {
			getCheckoutAdapter().selectPaymentMethod();
		}
		
		function covertToCacheableAddress(address){
			var cacheableAddress = {};
			_.each(address, function(value, key){
				if (!_.isFunction(value)) {
					cacheableAddress[key] = value;
				}
			});
			return cacheableAddress;
		}
		
		function hasAddressesChanged(){
			var currentShippingAddress = covertToCacheableAddress(getCheckoutAdapter().getShippingAddress()),
				currentBillingAddress = covertToCacheableAddress(quote.billingAddress());

			return !_.isEqual(shippingAddressCache, currentShippingAddress)
				|| (!_.isEqual({}, currentBillingAddress) && !_.isEqual(billingAddressCache, currentBillingAddress));
		}
		
		function storeShippingAddress(){
			return getCheckoutAdapter().storeShippingAddress();
		}
		
		function validateAddresses(){
			if (!quote.isVirtual()) {
				return getCheckoutAdapter().validateAddresses();
			} else {
				return true;
			}
		}
		
		function updateAddresses(callback) {
			if (!quote.isVirtual()) {
				storeShippingAddress();
				setShippingInformationAction().done(function(){
					if (typeof callback == 'function') {
						callback();
					}
					loadPaymentForm();
				});
			} else {
				if (typeof callback == 'function') {
					callback();
				}
				loadPaymentForm();
			}
		}
		
		function checkAddresses(){
			if (isActive() && validateAddresses()) {
				if (hasAddressesChanged()) {
					clearTimeout(addressTimeout);
					billingAddressCache = covertToCacheableAddress(quote.billingAddress());
					shippingAddressCache = covertToCacheableAddress(getCheckoutAdapter().getShippingAddress());
				}
			}
			setTimeout(checkAddresses, 100);
		}
		
		function getInstance() {
			return {
				canReplacePrimaryAction: canReplacePrimaryAction,
				isPrimaryActionReplaced: isPrimaryActionReplaced,
				replacePrimaryAction: replacePrimaryAction,
				resetPrimaryAction: resetPrimaryAction,
				selectPaymentMethod: selectPaymentMethod,
				hasAddressesChanged: hasAddressesChanged,
				validateAddresses: validateAddresses,
				updateAddresses: updateAddresses
			};
		}
		
		if (require.specified('postfinancecheckout_checkout_adapter')) {
			require(['postfinancecheckout_checkout_adapter'], function(adapter){
				pluginCheckoutAdapter = adapter;
				pluginCheckoutAdapter.formId = formId;
			//	checkAddresses(); Commenting this due it's affecting onestepcheckout plugin and doesn't affect regular checkout.
			});
		} else {
			defaultCheckoutAdapter.formId = formId;
			//checkAddresses(); Commenting this due it's affecting onestepcheckout plugin and doesn't affect regular checkout.
		}
		
		return getInstance();
	};
});
