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
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResourceModel;
use Psr\Log\LoggerInterface;
use PostFinanceCheckout\Payment\Api\TransactionInfoRepositoryInterface;
use PostFinanceCheckout\Payment\Model\ApiClient;
use PostFinanceCheckout\Payment\Model\Webhook\Request;
use PostFinanceCheckout\Sdk\Service\RefundService;

/**
 * Webhook listener to handle refunds.
 */
class RefundListener extends AbstractOrderRelatedListener
{

    /**
     *
     * @var ApiClient
     */
    private $apiClient;

    /**
     *
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     * @param OrderFactory $orderFactory
     * @param OrderResourceModel $orderResourceModel
     * @param CommandPoolInterface $commandPool
     * @param TransactionInfoRepositoryInterface $transactionInfoRepository
     * @param ApiClient $apiClient
     * @param LockManagerInterface $lockManager
     */
    public function __construct(ResourceConnection $resource, LoggerInterface $logger, OrderFactory $orderFactory,
        OrderResourceModel $orderResourceModel, CommandPoolInterface $commandPool,
        TransactionInfoRepositoryInterface $transactionInfoRepository, ApiClient $apiClient,
        LockManagerInterface $lockManager)
    {
        parent::__construct($resource, $logger, $orderFactory, $orderResourceModel, $commandPool,
            $transactionInfoRepository, $lockManager);
        $this->apiClient = $apiClient;
    }

    /**
     * Loads the refund for the webhook request.
     *
     * @param Request $request
     * @return \PostFinanceCheckout\Sdk\Model\Refund
     */
    protected function loadEntity(Request $request)
    {
        return $this->apiClient->getService(RefundService::class)->read($request->getSpaceId(), $request->getEntityId());
    }

    /**
     * Gets the transaction's ID.
     *
     * @param \PostFinanceCheckout\Sdk\Model\Refund $entity
     * @return int
     */
    protected function getTransactionId($entity)
    {
        return $entity->getTransaction()->getId();
    }
}
