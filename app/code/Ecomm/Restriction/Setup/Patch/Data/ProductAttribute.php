<?php
/**
 * @category   QorderQty
 * @package    QorderQty_Restriction
 * @author     pwc@gmail.com
 */

declare(strict_types=1);
namespace Ecomm\Restriction\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Zend_Validate_Exception;

/**
 * Add custom select attribute
 *
 * Class ProductAttribute
 */
class ProductAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $_moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $_eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->_moduleDataSetup = $moduleDataSetup;
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply the patch
     *
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->_moduleDataSetup]);

        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'product_limits', [
            'group' => 'Product Details',
                'type' => 'int',
                'frontend' => '',
                'label' => 'Product Quantity During Order',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true, 
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'is_wysiwyg_enabled'      => false,
                'unique' => false,
                'apply_to' => ''
        ]);

         $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'periods', [
            'group' => 'Product Details',
            'type' => 'text',
            'backend' => '',
            'frontend' => '',
            'label' => 'Periods',
            'input' => 'select',
            'class' => '',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
             'option' => ['values' => ['Months', 'Weeks']],
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'apply_to' => ''
        ]);


           $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'total_day', [
                'group' => 'Product Details',
                'type' => 'int',
                'frontend' => '',
                'label' => 'Total Periods',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true, 
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'is_wysiwyg_enabled'      => false,
                'unique' => false,
                'apply_to' => ''
        ]);

           $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'yes', [
                'group' => 'Product Details',
                 'type' => 'text',
                 'backend' => '',
                 'frontend' => '',
                 'label' => 'Enable Limit Restrictions',
                 'input' => 'select',
                 'class' => '',
                 'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                 'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                 'visible' => true,
                 'required' => true,
                 'user_defined' => false,
                 'default' => '',
                 'searchable' => false,
                 'filterable' => false,
                 'comparable' => false,
                 'visible_on_front' => false,
                 'used_in_product_listing' => true,
                 'unique' => false,
                 'apply_to' => ''
        ]);
    }



    /**
     * Get Dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get alisas
     *
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}