<?php
namespace PostFinanceCheckout\Payment\Setup\Patch\Data;
use \Magento\Framework\Setup\Patch\DataPatchInterface;
use \Magento\Framework\Setup\Patch\PatchVersionInterface;
use \Magento\Framework\Module\Setup\Migration;
use \Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Class AddSetupData
 * @package PostFinanceCheckout\Payment\Setup\Patch\Data
 */

class AddSetupDataState implements DataPatchInterface
{
    private $status;

    /**
     *
     * @param \PostFinanceCheckout\Payment\Model\Author $status
     */

    public function __construct(
        \Magento\Sales\Model\Order\Status $status
    ) {
        $this->status = $status;
    }

    /**
     * @details: It will create each status/state
     * @return:none
     */

    public function apply(){

        $statuses = array(array ('status'=>'processing_postfinancecheckout','label'=>'Hold Delivery'),
                          array('status'=>'shipped_postfinancecheckout','label'=>'Shipped'));

        foreach ($statuses as $statusData) {
            $this->status->addData($statusData);
            $this->status->getResource()->save($this->status);
            $this->status->assignState('processing', 'processing', true);
        }
    }

    /**
     * @return array:
     */

    public static function getDependencies(){
        return [];
    }

    /**
     * @return array:
     */

    public function getAliases(){
        return [];
    }
}
