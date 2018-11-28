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
namespace PostFinanceCheckout\Payment\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;
use PostFinanceCheckout\Payment\Api\RefundJobRepositoryInterface;
use PostFinanceCheckout\Payment\Model\ApiClient;
use PostFinanceCheckout\Sdk\Service\RefundService;

/**
 * Class to handle pending refund jobs.
 */
class Refund
{

    /**
     *
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     *
     * @var RefundJobRepositoryInterface
     */
    protected $_refundJobRepository;

    /**
     *
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     *
     * @var ApiClient
     */
    protected $_apiClient;

    /**
     *
     * @param LoggerInterface $logger
     * @param RefundJobRepositoryInterface $refundJobRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ApiClient $apiClient
     */
    public function __construct(LoggerInterface $logger, RefundJobRepositoryInterface $refundJobRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder, ApiClient $apiClient)
    {
        $this->_logger = $logger;
        $this->_refundJobRepository = $refundJobRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_apiClient = $apiClient;
    }

    public function execute()
    {
        $searchCriteria = $this->_searchCriteriaBuilder->setPageSize(100)->create();
        $refundJobs = $this->_refundJobRepository->getList($searchCriteria)->getItems();
        foreach ($refundJobs as $refundJob) {
            try {
                $this->_apiClient->getService(RefundService::class)->refund($refundJob->getSpaceId(),
                    $refundJob->getRefund());
            } catch (\PostFinanceCheckout\Sdk\ApiException $e) {
                if ($e->getResponseObject() instanceof \PostFinanceCheckout\Sdk\Model\ClientError) {
                    $this->_refundJobRepository->delete($refundJob);
                } else {
                    $this->_logger->critical($e);
                }
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }
    }
}