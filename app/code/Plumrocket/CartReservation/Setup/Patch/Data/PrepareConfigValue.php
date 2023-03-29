<?php
/**
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\CartReservation\Setup\Patch\Data;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Update the config value for an older version of the extension than 2.1.2.
 *
 * @since 2.5.0
 */
class PrepareConfigValue implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var CollectionFactory
     */
    private $configDataCollectionFactory;

    /**
     * @param CollectionFactory $configDataCollectionFactory
     */
    public function __construct(
        CollectionFactory $configDataCollectionFactory
    ) {
        $this->configDataCollectionFactory = $configDataCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $configCollection = $this->configDataCollectionFactory->create()
            ->addPathFilter('prcr/timer/format_separate');

        foreach ($configCollection as $config) {
            $newValue = preg_replace('/(<\/?)(?!br)\w*[^\s>\/]/m', '$1span', $config->getData('value'));
            $config->setData('path', $newValue);
        }

        $configCollection->save();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion(): string
    {
        return '2.1.2';
    }
}
