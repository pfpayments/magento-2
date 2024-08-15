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
use Psr\Log\LoggerInterface;

/**
 * Webhook listener command to handle completed transactions.
 */
class CompletedCommand extends AbstractCommand
{
    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var AuthorizedCommand
     */
    private $authorizedCommand;

    /**
     *
     * @param LoggerInterface $logger
     * @param AuthorizedCommand $authorizedCommand
     */
    public function __construct(AuthorizedCommand $authorizedCommand, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->authorizedCommand = $authorizedCommand;
    }

    /**
     *
     * @param \PostFinanceCheckout\Sdk\Model\Transaction $entity
     * @param Order $order
     * @return void
     */
    public function execute($entity, Order $order)
    {
        $this->logger->debug("CompletedCommand::execute state");
        $this->authorizedCommand->execute($entity, $order);
    }
}