<?php
/**
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\CartReservation\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Plumrocket\CartReservation\Helper\Product as ProductHelper;
use Plumrocket\CartReservation\Model\Attribute\Source\Enable as EnableSource;

/**
 * @since 2.5.0
 */
class CreateCategoryAttribute implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        if (! $eavSetup->getAttributeId(Category::ENTITY, ProductHelper::ATTRIBUTE_CODE)) {
            $entityTypeId = $eavSetup->getEntityTypeId(Category::ENTITY);
            $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
            $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, __('General Information'));

            $eavSetup->addAttribute(
                Category::ENTITY,
                ProductHelper::ATTRIBUTE_CODE,
                [
                    'global' => Attribute::SCOPE_STORE,
                    'label' => __('Cart Reservation Status'),
                    'type' => 'int',
                    'input' => 'select',
                    'source' => EnableSource::class,
                    'default' => EnableSource::INHERITED,

                    'backend' => '',
                    'frontend' => '',
                    'class' => '',

                    'visible' => true,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'required' => false,
                    'unique' => false,

                    'user_defined' => 0,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'apply_to' => '',
                    'position' => 467
                ]
            );

            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $attributeGroupId,
                ProductHelper::ATTRIBUTE_CODE,
                '467'
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(Category::ENTITY, ProductHelper::ATTRIBUTE_CODE);
        $this->moduleDataSetup->getConnection()->endSetup();
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
}
