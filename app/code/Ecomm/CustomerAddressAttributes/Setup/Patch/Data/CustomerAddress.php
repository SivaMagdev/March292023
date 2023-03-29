<?php declare(strict_types=1);
namespace Ecomm\CustomerAddressAttributes\Setup\Patch\Data;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddressAttribute
 */
class CustomerAddress implements DataPatchInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * AddressAttribute constructor.
     *
     * @param Config              $eavConfig
     * @param EavSetupFactory     $eavSetupFactory
     */
    public function __construct(
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();


        $eavSetup->addAttribute('customer_address', 'hin_Start', [
        
            'type' => 'datetime',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
            'frontend' => 'Magento\Eav\Model\Entity\Attribute\Frontend\Datetime',
            'label' => 'HIN Start Date',
            'input' => 'date',
             'visible'          => true,
            'required'         => false,
            'user_defined'     => true,
            'system'           => false,
            'group'            => 'General',
            'global'           => true,
             'position'     => 1,
            'visible_on_front' => false,
         ]);

        
          $customAttribute = $this->eavConfig->getAttribute('customer_address', 'hin_Start');

           $customAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address',
             'customer_address_edit',
             'customer_register_address']
            );
             $customAttribute->save();

         $eavSetup->addAttribute('customer_address', 'hin_end', [
        
                'type' => 'datetime',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
                'frontend' => 'Magento\Eav\Model\Entity\Attribute\Frontend\Datetime',
            'label' => 'HIN End Date',
            'input' => 'date',
            'visible'          => true,
            'required'         => false,
            'user_defined'     => true,
            'system'           => false,
            'group'            => 'General',
            'global'           => true,
             'position'     => 2,
            'visible_on_front' => false,
        ]);

                 $customAttribute = $this->eavConfig->getAttribute('customer_address', 'hin_end');

    $customAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address',
             'customer_address_edit',
             'customer_register_address']
        );
   $customAttribute->save();


 $eavSetup->addAttribute('customer_address', 'member_id', [
            'type'             => 'varchar',
            'input'            => 'text',
            'label'            => 'Member ID',
            'visible'          => true,
            'required'         => false,
            'user_defined'     => true,
            'system'           => false,
            'group'            => 'General',
            'position'         => 4,
            'global'           => true,
            'visible_on_front' => false,
        ]);

        $customAttribute = $this->eavConfig->getAttribute('customer_address', 'member_id');

        $customAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address',
             'customer_address_edit',
             'customer_register_address']
        );
          $customAttribute->save();

            $eavSetup->addAttribute('customer_address', 'three_four_b_id', [
            'type'             => 'varchar',
            'input'            => 'text',
            'label'            => '340b ID',
            'visible'          => true,
            'required'         => false,
            'user_defined'     => true,
            'system'           => false,
            'group'            => 'General',
            'global'           => true,
            'position'         => 4,
            'visible_on_front' => false,
        ]);

        $customAttribute = $this->eavConfig->getAttribute('customer_address', 'three_four_b_id');

        $customAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address',
             'customer_address_edit',
             'customer_register_address']
        );
          $customAttribute->save();
          $eavSetup->addAttribute('customer_address', 'three_four_b_start', [
          'type' => 'datetime',
          'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
          'frontend' => 'Magento\Eav\Model\Entity\Attribute\Frontend\Datetime',
          'label' => '340b Start Date',
          'input' => 'date',
          'visible'  => true,
            'required' => false,
            'user_defined'  => true,
            'system'  => false,
            'group'    => 'General',
            'global'    => true,
            'position'     => 4,
            'visible_on_front' => false,
        ]);

        
          $customAttribute = $this->eavConfig->getAttribute('customer_address', 'three_four_b_start');
           
          
           $customAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address',
             'customer_address_edit',
             'customer_register_address']
        );
            $customAttribute->save();
            $eavSetup->addAttribute('customer_address', 'three_four_b_end', [
        
         'type' => 'datetime',
         'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
         'frontend' => 'Magento\Eav\Model\Entity\Attribute\Frontend\Datetime',
         'label' => '340b End Date',
         'input' => 'date',
         'visible'          => true,
            'required'         => false,
            'user_defined'     => true,
            'system'           => false,
            'group'            => 'General',
            'global'           => true,
             'position'     => 5,
            'visible_on_front' => false,
        ]);
   
          $customAttribute = $this->eavConfig->getAttribute('customer_address', 'three_four_b_end');

          
           $customAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address',
             'customer_address_edit',
             'customer_register_address']
        );
           $customAttribute->save();

    }


    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
