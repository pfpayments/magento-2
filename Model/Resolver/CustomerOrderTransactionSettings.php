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
namespace PostFinanceCheckout\Payment\Model\Resolver;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Customer\Model\Session;
use Magento\GraphQl\Model\Query\ContextInterface;
use PostFinanceCheckout\Payment\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use PostFinanceCheckout\Payment\Model\Service\Quote\TransactionService as TransactionQuoteService;
use PostFinanceCheckout\Payment\Model\Service\Order\TransactionService as TransactionOrderService;

class CustomerOrderTransactionSettings implements ResolverInterface
{
	/**
	 *
	 * @var Session
	 */
	private $customerSession;

	/**
	 *
	 * @var CheckoutSession
	 */
	private $checkoutSession;

	/**
	 *
	 * @var GetCustomer
	 */
	private $getCustomer;

	/**
	 *
	 * @var OrderRepositoryInterface
	 */
	private $orderRepository;

	/**
	 *
	 * @var TransactionQuoteService
	 */
	private $transactionQuoteService;

	/**
	 *
	 * @var TransactionOrderService
	 */
	private $transactionOrderService;

	/**
	 *
	 * @var LoggerInterface
	 */
	private $logger;


	public function __construct(
		Session                  $customerSession,
		CheckoutSession          $checkoutSession,
		GetCustomer              $getCustomer,
		OrderRepositoryInterface $orderRepository,
		TransactionQuoteService  $transactionQuoteService,
		TransactionOrderService  $transactionOrderService,
		LoggerInterface          $logger
	) {
		$this->customerSession = $customerSession;
		$this->checkoutSession = $checkoutSession;
		$this->getCustomer = $getCustomer;
		$this->logger = $logger;
		$this->transactionQuoteService = $transactionQuoteService;
		$this->transactionOrderService = $transactionOrderService;
		$this->orderRepository = $orderRepository;
	}

	public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
	{
		//only perform validations if the user is anonymous.
		if ($this->checkoutSession->getQuote()->getCustomerId()) {
			/** @var ContextInterface $context */
			if (false === $context->getExtensionAttributes()->getIsCustomer()) {
				throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
			}

			$customer = $this->getCustomer->execute($context);
			if ($this->customerSession !== null && $customer->getId() !== $this->customerSession->getCustomer()->getId()) {
				throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
			}
		}

		try {
			$orderId = $args['order_id'];
			$integrationType = $args['integration_type'];
			return $this->getTransactionSettings($orderId, $integrationType);
		} catch (NoSuchEntityException $e) {
			$this->logger->critical($e);
			throw new GraphQlNoSuchEntityException(__($e->getMessage()));
		}
	}

	/**
	 * Gets the transaction settings to use their custom payment integration
	 *
	 * @return array
	 * @throws NoSuchEntityException
	 * @throws LocalizedException
	 */
	private function getTransactionSettings(string $incrementId, string $integrationType)
	{
		/** @var \Magento\Sales\Model\Order  $order */
		$order = $this->orderRepository->getOrderByIncrementId($incrementId);
		$transaction = $this->transactionQuoteService->getTransaction(
			$order->getPostfinancecheckoutSpaceId(),
			$order->getPostfinancecheckoutTransactionId()
		);
		$url = $this->transactionOrderService->getTransactionPaymentUrl($order, $integrationType);

		return [
			'order_id' => $order->getId(),
			'transaction_id' => $transaction->getId(),
			'transaction_state' => $transaction->getState(),
			'payment_url' => $url,
			'integration_type' => $integrationType
		];
	}
}