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
namespace PostFinanceCheckout\Payment\Model\Webhook\Listener;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResourceModel;
use Psr\Log\LoggerInterface;
use PostFinanceCheckout\Payment\Api\TransactionInfoRepositoryInterface;
use PostFinanceCheckout\Payment\Model\Webhook\ListenerInterface;
use PostFinanceCheckout\Payment\Model\Webhook\Request;

/**
 * Abstract webhook listener for order related entities.
 */
abstract class AbstractOrderRelatedListener implements ListenerInterface
{

    /**
     *
     * @var ResourceConnection
     */
    private $resource;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     *
     * @var OrderResourceModel
     */
    private $orderResourceModel;

    /**
     *
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     *
     * @var TransactionInfoRepositoryInterface
     */
    private $transactionInfoRepository;

    /**
     *
     * @var lockManager
     */
    protected $lockManager;

    /**
     *
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     * @param OrderFactory $orderFactory
     * @param OrderResourceModel $orderResourceModel
     * @param CommandPoolInterface $commandPool
     * @param TransactionInfoRepositoryInterface $transactionInfoRepository
     * @param LockManagerInterface $lockManager
     */
    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger,
        OrderFactory $orderFactory,
        OrderResourceModel $orderResourceModel,
        CommandPoolInterface $commandPool,
        TransactionInfoRepositoryInterface $transactionInfoRepository,
        LockManagerInterface $lockManager
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->orderResourceModel = $orderResourceModel;
        $this->commandPool = $commandPool;
        $this->transactionInfoRepository = $transactionInfoRepository;
        $this->lockManager = $lockManager;
    }

    public function execute(Request $request)
    {
        $entity = $this->loadEntity($request);
        $order = $this->loadOrder($this->getOrderId($entity));

        if (!($order instanceof Order)) {
            return;
        }
        $transactionId = $order->getPostfinancecheckoutTransactionId();
        try {
            $connection = $this->beginTransaction();
            if ($transactionId != $this->getTransactionId($entity)) {
                $this->logger->warning(
                    'PostFinance Checkout webhook: The transaction ID on the order ' . $order->getId() .
                    ' does not match the webhook\'s: ' . $this->getTransactionId($entity));
                $connection->commit();
                return;
            }
            $this->lock($transactionId);
            $this->process($this->loadEntity($request), $this->loadOrder($order->getId()));
            $this->unlock($transactionId);
            $connection->commit();
        } catch (\Exception $e) {
            $this->unlock($transactionId);
            $connection->rollback();
            $this->logger->critical($e);
            throw $e;
        }
    }

    /**
     * Starts a database transaction with isolation level 'read uncommitted'.
     *
     * In case of two parallel requests linked to the same order, data written to the database by the first will
     * not be up-to-date in the second. This can lead to processing the same data multiple times. By setting the
     * isolation level to 'read uncommitted' this issue can be avoided.
     *
     * An alternative solution to this problem would be to use optimistic locking. However, this could lead to database
     * rollbacks and as for example updating the order status could lead to triggering further processes which may not
     * propertly handle rollbacks, this could result in inconsistencies.
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function beginTransaction()
    {
        $connection = $this->resource->getConnection('sales');
        $connection->rawQuery("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;");
        $connection->beginTransaction();
        return $connection;
    }

    /**
     * Loads the order by the given ID.
     *
     * @param int $orderId
     * @return Order|NULL
     */
    private function loadOrder($orderId)
    {
        if (! $orderId) {
            return null;
        }

        $order = $this->orderFactory->create();
        $this->orderResourceModel->load($order, $orderId);
        if (! $order->getEntityId()) {
            return null;
        } else {
            return $order;
        }
    }

    /**
     * Gets the ID of the order linked to the given entity.
     *
     * @param mixed $entity
     * @return int|NULL
     */
    private function getOrderId($entity)
    {
        try {
            $transactionInfo = $this->transactionInfoRepository->getByTransactionId($entity->getLinkedSpaceId(),
                $this->getTransactionId($entity));
            return $transactionInfo->getOrderId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Creates a lock to prevent concurrency.
     *
     * @return bool
     */
    protected function lock(string $lockId): bool
    {
        return $this->lockManager->lock($lockId, 1);
    }

    /**
     * Releases our lock.
     *
     * @return bool
     */
    protected function unlock(string $lockId): bool
    {
        return $this->lockManager->unlock($lockId);
    }

    /**
     * Actually processes the order related webhook request.
     *
     * @param mixed $entity
     * @param Order $order
     * @return void
     */
    protected function process($entity, Order $order)
    {
        try {
            $this->commandPool->get(\strtolower($entity->getState()))
                ->execute($entity, $order);
        } catch (NotFoundException $e) {
            // If the command cannot be found, we ignore it.
        }
    }

    /**
     * Loads the entity for the webhook request.
     *
     * @param Request $request
     * @return mixed
     */
    abstract protected function loadEntity(Request $request);

    /**
     * Gets the transaction's id linked to the entity.
     *
     * @param mixed $entity
     * @return int
     */
    abstract protected function getTransactionId($entity);
}
