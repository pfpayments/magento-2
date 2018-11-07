<?php
/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://www.postfinance.ch/).
 *
 * @package PostFinanceCheckout_Payment
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
namespace PostFinanceCheckout\Payment\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use PostFinanceCheckout\Payment\Api\RefundJobRepositoryInterface;
use PostFinanceCheckout\Payment\Api\Data\RefundJobInterface;
use PostFinanceCheckout\Payment\Api\Data\RefundJobSearchResultsInterfaceFactory;
use PostFinanceCheckout\Payment\Model\ResourceModel\RefundJob as RefundJobResource;
use PostFinanceCheckout\Payment\Model\ResourceModel\RefundJob\CollectionFactory as RefundJobCollectionFactory;

/**
 * Refund job CRUD service.
 */
class RefundJobRepository implements RefundJobRepositoryInterface
{

    /**
     *
     * @var RefundJobFactory
     */
    protected $_refundJobFactory;

    /**
     *
     * @var RefundJobCollectionFactory
     */
    protected $_refundJobCollectionFactory;

    /**
     *
     * @var RefundJobSearchResultsInterfaceFactory
     */
    protected $_searchResultsFactory;

    /**
     *
     * @var RefundJobResource
     */
    protected $_resource;

    /**
     *
     * @var CollectionProcessorInterface
     */
    protected $_collectionProcessor;

    /**
     *
     * @param RefundJobFactory $refundJobFactory
     * @param RefundJobCollectionFactory $refundJobCollectionFactory
     * @param RefundJobSearchResultsInterfaceFactory $searchResultsFactory
     * @param RefundJobResource $resource
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(RefundJobFactory $refundJobFactory,
        RefundJobCollectionFactory $refundJobCollectionFactory,
        RefundJobSearchResultsInterfaceFactory $searchResultsFactory, RefundJobResource $resource,
        CollectionProcessorInterface $collectionProcessor)
    {
        $this->_refundJobFactory = $refundJobFactory;
        $this->_refundJobCollectionFactory = $refundJobCollectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_resource = $resource;
        $this->_collectionProcessor = $collectionProcessor;
    }

    /**
     * Create refund job
     *
     * @param RefundJobInterface $object
     * @return RefundJob
     * @throws CouldNotSaveException
     */
    public function save(RefundJobInterface $object)
    {
        try {
            $this->_resource->save($object);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(\__('Could not save the refund job: %1', $exception->getMessage()),
                $exception);
        }
        return $object;
    }

    /**
     * Get job about refund job by entity ID
     *
     * @param int $entityId
     * @return RefundJob
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function get($entityId)
    {
        if (! $entityId) {
            throw new InputException(\__('ID required'));
        }

        /** @var RefundJob $object */
        $object = $this->refundJobFactory->create();
        $this->_resource->load($object, $entityId);
        if (! $object->getEntityId()) {
            throw new NoSuchEntityException(\__('Requested entity does not exist'));
        }
        return $object;
    }

    /**
     * Get job about refund job by order ID
     *
     * @param int $orderId
     * @return RefundJob
     * @throws NoSuchEntityException
     */
    public function getByOrderId($orderId)
    {
        if (! $orderId) {
            throw new InputException(\__('Order ID required'));
        }

        /** @var RefundJob $object */
        $object = $this->_refundJobFactory->create();
        $this->_resource->load($object, $orderId, RefundJobInterface::ORDER_ID);
        if (! $object->getEntityId()) {
            throw new NoSuchEntityException(\__('Requested entity does not exist'));
        }
        return $object;
    }

    /**
     * Get job about refund job by external ID
     *
     * @param string $externalId
     * @return RefundJob
     * @throws NoSuchEntityException
     */
    public function getByExternalId($externalId)
    {
        if (! $externalId) {
            throw new InputException(\__('External ID required'));
        }

        /** @var RefundJob $object */
        $object = $this->_refundJobFactory->create();
        $this->_resource->load($object, $externalId, RefundJobInterface::EXTERNAL_ID);
        if (! $object->getEntityId()) {
            throw new NoSuchEntityException(\__('Requested entity does not exist'));
        }
        return $object;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \PostFinanceCheckout\Payment\Model\ResourceModel\RefundJob\Collection $collection */
        $collection = $this->_refundJobCollectionFactory->create();

        $this->_collectionProcessor->process($searchCriteria, $collection);

        /** @var \PostFinanceCheckout\Payment\Api\Data\RefundJobSearchResultsInterface $searchResults */
        $searchResults = $this->_searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function delete(RefundJobInterface $object)
    {
        try {
            $this->_resource->delete($object);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(\__('Could not delete the refund job: %1', $exception->getMessage()));
        }
        return true;
    }

    public function deleteByIdentifier($entityId)
    {
        $this->delete($this->get($entityId));
    }
}