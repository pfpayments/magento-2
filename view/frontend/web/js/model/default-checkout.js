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
	'jquery'
], function(
	$
){
	'use strict';
	return {
		formId: null,
		primaryActionReplaced: false,
		
		canReplacePrimaryAction: function(){
			return true;
		},
		
		isPrimaryActionReplaced: function(){
			return this.primaryActionReplaced;
		},
		
		replacePrimaryAction: function(label) {
			this.getSubmitButton().find('span').html(label);
			this.primaryActionReplaced = true;
		},
		
		resetPrimaryAction: function(){
			this.getSubmitButton().find('span').html(this.getSubmitButton().attr('title'));
			this.primaryActionReplaced = false;
		},
		
		selectPaymentMethod: function(){},
		
		getSubmitButton: function(){
			return $('#' + this.formId).parents('.payment-method-content').find('button.checkout');
		},
		
		getShippingAddress: function(){},
		
		storeShippingAddress: function(){},
		
		validateAddresses: function(){
			return true;
		}
	};
});