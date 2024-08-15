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

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Webhook listener command pool.
 */
class CommandPool implements CommandPoolInterface
{

    /**
     *
     * @var \Magento\Framework\ObjectManager\TMap|CommandInterface[]
     */
    private $commands;

    /**
     *
     * @param TMapFactory $tmapFactory
     * @param array $commands
     */
    public function __construct(TMapFactory $tmapFactory, array $commands = [])
    {
        $this->commands = $tmapFactory->create([
            'array' => $commands,
            'type' => CommandInterface::class
        ]);
    }

    /**
     * Retrieves command.
     *
     * @param string $commandCode
     * @return CommandInterface
     * @throws NotFoundException
     */
    public function get($commandCode)
    {
        if (! isset($this->commands[$commandCode])) {
            throw new NotFoundException(\__('Command %1 does not exist.', $commandCode));
        }

        return $this->commands[$commandCode];
    }
}