<?php
/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @package PostFinanceCheckout_Payment
 * @author PostFinance Ltd (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)

 */
namespace PostFinanceCheckout\Payment\Gateway\Command;

use Magento\Framework\Math\Random;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use PostFinanceCheckout\Payment\Api\TokenInfoRepositoryInterface;
use PostFinanceCheckout\Payment\Helper\Data as Helper;
use PostFinanceCheckout\PluginCore\Log\LoggerInterface;
use PostFinanceCheckout\PluginCore\Token\State as CoreTokenState;
use PostFinanceCheckout\PluginCore\Token\Token as CoreToken;

/**
 * Payment gateway command to initialize a payment.
 */
class InitializeCommand implements CommandInterface
{

    /**
     *
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     *
     * @var Random
     */
    private $random;

    /**
     *
     * @var Helper
     */
    private $helper;

    /**
     *
     * @var TokenInfoRepositoryInterface
     */
    private $tokenInfoRepository;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param Random $random
     * @param Helper $helper
     * @param TokenInfoRepositoryInterface $tokenInfoRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Random $random,
        Helper $helper,
        TokenInfoRepositoryInterface $tokenInfoRepository,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->random = $random;
        $this->helper = $helper;
        $this->tokenInfoRepository = $tokenInfoRepository;
        $this->logger = $logger;
    }

    /**
     * An invoice is created and the transaction updated to match the order and confirmed.
     *
     * The order state is set to {@link Order::STATE_PENDING_PAYMENT}.
     *
     * @param array $commandSubject
     * @return void
     * @throws \InvalidArgumentException
     * @see CommandInterface::execute()
     */
    public function execute(array $commandSubject)
    {
        $stateObject = SubjectReader::readStateObject($commandSubject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = SubjectReader::readPayment($commandSubject)->getPayment();

        /** @var Order $order */
        $order = $payment->getOrder();

        $order->setCanSendNewEmailFlag(false);
        $payment->setAmountAuthorized($order->getTotalDue());
        $payment->setBaseAmountAuthorized($order->getBaseTotalDue());

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($order->getQuoteId());

        if (! $quote->getPostfinancecheckoutSpaceId() || ! $quote->getPostfinancecheckoutTransactionId()) {
            $this->logger->error('Initialize failed: no transaction set on the quote.', [
                'quoteId' => $quote->getId(),
            ]);
            throw new \InvalidArgumentException('The PostFinance Checkout payment transaction is not set on the quote.');
        }

        if ($order->getPostfinancecheckoutSpaceId() != null ||
            $order->getPostfinancecheckoutTransactionId() != null) {
            $this->logger->error('Initialize failed: transaction already set on the order.', [
                'orderId' => $order->getIncrementId(),
            ]);
            throw new \InvalidArgumentException(
                'The PostFinance Checkout payment transaction has already been set on the order.'
            );
        }

        $order->setPostfinancecheckoutSpaceId($quote->getPostfinancecheckoutSpaceId());
        $order->setPostfinancecheckoutTransactionId($quote->getPostfinancecheckoutTransactionId());
        $order->setPostfinancecheckoutSecurityToken($this->random->getUniqueHash());

        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);

        if ($this->helper->isAdminArea()) {
            // Tell the order to apply the charge flow after it is saved.
            $order->setPostfinancecheckoutChargeFlow(true);
            $order->setPostfinancecheckoutToken($this->getToken($quote));
        }

        $this->logger->info('Initialize completed.', [
            'quoteId' => $quote->getId(),
            'transactionId' => $quote->getPostfinancecheckoutTransactionId(),
        ]);
    }

    /**
     * Retrieve payment token from quote for admin orders.
     *
     * @param Quote $quote
     * @return void|CoreToken
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getToken(Quote $quote)
    {
        if ($this->helper->isAdminArea()) {
            $tokenInfoId = $quote->getPayment()->getData('postfinancecheckout_token');
            if ($tokenInfoId) {
                $tokenInfo = $this->tokenInfoRepository->get($tokenInfoId);
                return new CoreToken(
                    id: (int) $tokenInfo->getTokenId(),
                    state: CoreTokenState::tryFrom($tokenInfo->getState()) ?? CoreTokenState::ACTIVE,
                );
            }
        }
    }
}
