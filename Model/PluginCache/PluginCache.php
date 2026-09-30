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
declare(strict_types=1);

namespace PostFinanceCheckout\Payment\Model\PluginCache;

use PostFinanceCheckout\PluginCore\GlobalData\LabelDescriptor\LabelDescriptor;
use PostFinanceCheckout\PluginCore\GlobalData\LabelDescriptor\LabelDescriptorCollection;
use PostFinanceCheckout\PluginCore\GlobalData\LabelDescriptorGroup\LabelDescriptorGroup;
use PostFinanceCheckout\PluginCore\GlobalData\LabelDescriptorGroup\LabelDescriptorGroupCollection;
use PostFinanceCheckout\PluginCore\Localization\LocalizedString;
use PostFinanceCheckout\PluginCore\SharedKernel\CacheInterface;

/**
 * Exposes the module's Magento cache type to plugin-core through plugin-core's CacheInterface.
 */
class PluginCache implements CacheInterface
{
    /**
     * Every class that can appear inside a cached value.
     *
     * Without this list, anyone who can write to the cache could make PHP create
     * objects of any class and run their code. Classes not on the list come back
     * as a placeholder object that can't run anything.
     *
     * When plugin-core starts caching another kind of object, add its classes here.
     */
    private const ALLOWED_CLASSES = [
        LabelDescriptor::class,
        LabelDescriptorCollection::class,
        LabelDescriptorGroup::class,
        LabelDescriptorGroupCollection::class,
        LocalizedString::class,
    ];

    /**
     * @param CacheType $cache
     */
    public function __construct(
        private readonly CacheType $cache
    ) {
    }

    /**
     * @inheritdoc
     *
     * @param mixed $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(mixed $key, mixed $default = null): mixed
    {
        $data = $this->cache->load((string) $key);
        if (!is_string($data)) {
            return $default;
        }
        // phpcs:ignore Magento2.Security.InsecureFunction
        $value = unserialize($data, ['allowed_classes' => self::ALLOWED_CLASSES]);
        // phpcs:ignore Magento2.Security.InsecureFunction
        if ($value === false && $data !== serialize(false)) {
            return $default;
        }
        return $value;
    }

    /**
     * @inheritdoc
     *
     * @param mixed $key
     * @param mixed $value
     * @param mixed|null $ttl
     * @return bool
     */
    public function set(mixed $key, mixed $value, mixed $ttl = null): bool
    {
        $lifetime = $this->toSeconds($ttl);
        if ($lifetime !== null && $lifetime <= 0) {
            return $this->delete($key);
        }
        // phpcs:ignore Magento2.Security.InsecureFunction
        $data = serialize($value);
        return $this->cache->save($data, (string) $key, [], $lifetime);
    }

    /**
     * @inheritdoc
     *
     * @param mixed $key
     * @return bool
     */
    public function delete(mixed $key): bool
    {
        return $this->cache->remove((string) $key);
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function clear(): bool
    {
        return $this->cache->clean();
    }

    /**
     * @inheritdoc
     *
     * @param mixed $keys
     * @param mixed|null $default
     * @return iterable
     */
    public function getMultiple(mixed $keys, mixed $default = null): iterable
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }
        return $values;
    }

    /**
     * @inheritdoc
     *
     * @param mixed $values
     * @param mixed|null $ttl
     * @return bool
     */
    public function setMultiple(mixed $values, mixed $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            $success = $this->set($key, $value, $ttl) && $success;
        }
        return $success;
    }

    /**
     * @inheritdoc
     *
     * @param mixed $keys
     * @return bool
     */
    public function deleteMultiple(mixed $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            $success = $this->delete($key) && $success;
        }
        return $success;
    }

    /**
     * @inheritdoc
     *
     * @param mixed $key
     * @return bool
     */
    public function has(mixed $key): bool
    {
        return $this->cache->test((string) $key) !== false;
    }

    /**
     * Converts a PSR-16 TTL to the lifetime in seconds that Magento's save() expects.
     *
     * @param mixed $ttl
     * @return int|null
     */
    private function toSeconds(mixed $ttl): ?int
    {
        if ($ttl instanceof \DateInterval) {
            $now = new \DateTimeImmutable();
            return $now->add($ttl)->getTimestamp() - $now->getTimestamp();
        }
        return $ttl === null ? null : (int) $ttl;
    }
}
