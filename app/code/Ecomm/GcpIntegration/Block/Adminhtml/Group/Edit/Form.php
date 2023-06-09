<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecomm\GcpIntegration\Block\Adminhtml\Group\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\App\ResourceConnection;

/**
 * Adminhtml customer groups edit form
 */
class Form extends \Magento\Customer\Block\Adminhtml\Group\Edit\Form
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Tax\Model\TaxClass\Source\Customer $taxCustomer
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\Data\GroupInterfaceFactory $groupDataFactory
     * @param ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Tax\Model\TaxClass\Source\Customer $taxCustomer,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\Data\GroupInterfaceFactory $groupDataFactory,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $taxCustomer,
            $taxHelper,
            $groupRepository,
            $groupDataFactory,
            $data
        );
    }

    /**
     * Prepare form for render
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $groupId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_GROUP_ID);
        /** @var \Magento\Customer\Api\Data\GroupInterface $customerGroup */
        if ($groupId === null) {
            $customerGroup = $this->groupDataFactory->create();
            $defaultCustomerTaxClass = $this->_taxHelper->getDefaultCustomerTaxClass();
        } else {
            $customerGroup = $this->_groupRepository->getById($groupId);
            $defaultCustomerTaxClass = $customerGroup->getTaxClassId();
        }

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Group Information')]);

        $validateClass = sprintf(
            'required-entry validate-length maximum-length-%d',
            \Magento\Customer\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
        );
        $name = $fieldset->addField(
            'customer_group_code',
            'text',
            [
                'name' => 'code',
                'label' => __('Group Name'),
                'title' => __('Group Name'),
                'note' => __(
                    'Maximum length must be less then %1 characters.',
                    \Magento\Customer\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
                ),
                'class' => $validateClass,
                'required' => true
            ]
        );

        if ($customerGroup->getId() == 0 && $customerGroup->getCode()) {
            $name->setDisabled(true);
        }

        $fieldset->addField(
            'tax_class_id',
            'select',
            [
                'name' => 'tax_class',
                'label' => __('Tax Class'),
                'title' => __('Tax Class'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->_taxCustomer->toOptionArray(),
            ]
        );

        if ($customerGroup->getId() !== null) {
            // If edit add id
            $form->addField('id', 'hidden', ['name' => 'id', 'value' => $customerGroup->getId()]);
        }

        $fieldset->addField(
            'gpo_contract_id',
            'text',
            [
                'name' => 'gpo_contract_id',
                'label' => __('GPO Contract Id'),
                'title' => __('GPO Contract Id'),
                'note' => __(
                    'Maximum length must be less then %1 characters.',
                    \Magento\Customer\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
                )
            ]
        );

        if ($this->_backendSession->getCustomerGroupData()) {
            $form->addValues($this->_backendSession->getCustomerGroupData());
            $this->_backendSession->setCustomerGroupData(null);
        } else {
            $gpoContractId  = $this->getGpoContractId($customerGroup->getId());
            // TODO: need to figure out how the DATA can work with forms
            $form->addValues(
                [
                    'id' => $customerGroup->getId(),
                    'customer_group_code' => $customerGroup->getCode(),
                    'tax_class_id' => $defaultCustomerTaxClass,
                    'gpo_contract_id' => $gpoContractId
                ]
            );
        }

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('customer/*/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }

    /**
     * Get Sap Customer Group Id.
     *
     * @param int $groupId
     * @return mixed|string
     */
    private function getGpoContractId($groupId)
    {
        try {
            $sapGroupId = '';
            $tableName = $this->resourceConnection->getTableName('customer_group');
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from(
                    ['g' => $tableName],
                    ['gpo_contract_id']
                )
                ->where("g.customer_group_id = :customer_group_id");
            $bind = ['customer_group_id' => $groupId];
            $records = $connection->fetchAll($select, $bind);
            if (isset($records[0]['gpo_contract_id'])) {
                $sapGroupId = $records[0]['gpo_contract_id'];
            }
            return $sapGroupId;
        } catch (\Exception $e) {
            return '';
        }
    }
}
