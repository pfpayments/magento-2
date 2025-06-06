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
namespace PostFinanceCheckout\Payment\Model\Webhook\Listener\Transaction;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Webhook listener command to handle fulfilled transactions.
 */
class FulfillCommand extends AbstractCommand
{

    /**
     *
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     *
     * @var AuthorizedCommand
     */
    private $authorizedCommand;

    /**
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param AuthorizedCommand $authorizedCommand
     */
    public function __construct(OrderRepositoryInterface $orderRepository, AuthorizedCommand $authorizedCommand)
    {
        $this->orderRepository = $orderRepository;
        $this->authorizedCommand = $authorizedCommand;
    }

    /**
     *
     * @param \PostFinanceCheckout\Sdk\Model\Transaction $entity
     * @param Order $order
     */
    public function execute($entity, Order $order)
    {
        $this->authorizedCommand->execute($entity, $order);

        if ($order->getState() == Order::STATE_PAYMENT_REVIEW) {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();
            $payment->setIsTransactionApproved(true);
            $payment->update(false);
        } elseif ($order->getStatus() == Order::STATE_PROCESSING) {
            // $order->setState(Order::STATE_PROCESSING);
            $order->addStatusToHistory(true, \__('The order can be fulfilled now.'));
        }
        $this->orderRepository->save($order);
    }
}
