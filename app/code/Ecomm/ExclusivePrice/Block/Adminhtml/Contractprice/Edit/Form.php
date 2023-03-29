<?php

namespace Ecomm\ExclusivePrice\Block\Adminhtml\ContractPrice\Edit;

use Ecomm\ExclusivePrice\Controller\RegistryConstants;

/**
 * Adminhtml customer groups edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Ecomm\PriceEngine\Model\ExclusivePriceFactory
     */
    protected $_contractpriceFactory;

    protected $_customerGroup;

    protected $groupRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Ecomm\PriceEngine\Model\ExclusivePriceFactory $exclusivepriceFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Ecomm\ExclusivePrice\Model\ContractPriceFactory $contractpriceFactory,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Backend\Model\Auth\Session $authSession, 

        array $data = []
    ) {
        $this->_customerGroup = $customerGroup;
        $this->_contractpriceFactory = $contractpriceFactory;
        $this->authSession = $authSession;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form for render
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $editForm = '';
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $contractpriceId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CONTRACTPRICE_ID);

        if ($contractpriceId === null) {
            $contractprice = $this->_contractpriceFactory->create();
        } else {
            $contractprice = $this->_contractpriceFactory->create()->load($contractpriceId);
            $editForm = 'edit';
        }

        //echo '<pre>test'.print_r($regularprice->getData(), true).'<pre>';

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Contract Information')]);

        $contract_id = $fieldset->addField(
            'contract_id',
            'text',
            [
                'name' => 'contract_id',
                'label' => __('Contract Id'),
                'title' => __('Contract Id'),
                'required' => true,
            ]
        );

        $contract_type = $fieldset->addField(
            'contract_type',
            'select',
            [
                'name' => 'contract_type',
                'label' => __('Contract Type'),
                'title' => __('Contract Type'),
                'required' => true,
                'value' => '',
                'values' => [
                    ['label' => '', 'value' => ''],
                    ['label' => 'GPO', 'value' => 'gpo'],
                    ['label' => 'RCA', 'value' => 'rca'],
                    ['label' => 'Sub Wac', 'value' => 'sub_wac'],
                    ['label' => 'PHS Indirect', 'value' => 'phs_indirect'],
                ],
            ]
        );


        $gpo_name = $fieldset->addField(
            'gpo_id',
            'select',
            [
                'name' => 'gpo_id',
                'label' => __('Gpo_Name'),
                'title' => __('Gpo_Name'),
                'value' => '',
                'values' => $this->getCustomerGroups(),
                'required' => true,

            ]
        );

        $name = $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );
        $is_dsh = $fieldset->addField(
            'is_dsh',
            'checkbox',
            [
                'name' => 'is_dsh',
                'label' => __('DSH'),
                'title' => __('DSH'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'required' => false,
            ]
        );


        /*$status = $fieldset->addField(
        'status',
        'select',
        [
        'name'      => 'status',
        'label'     => __('Status'),
        'options'   => $this->_thanaStatus->toOptionArray()
        ]
        );*/

        /*if ($regularprice->getId() == 0 && $regularprice->getName()) {
        $name->setDisabled(true);
        }*/

        if ($contractprice->getId() !== null) {
            // If edit add id
            $form->addField('entity_id', 'hidden', ['name' => 'entity_id', 'value' => $contractprice->getId()]);
        }
        // var_dump($contractprice->getGpoName());
        // exit;
        // $group = $this->groupRepository->getById($contractprice->getGpoName());
        // var_dump($contractprice->getEntityId());
        // exit;

        // TODO: need to figure out how the DATA can work with forms
        $form->addValues(
            [
                'entity_id' => $contractprice->getEntityId(),
                'contract_id' => $contractprice->getContractId(),
                'contract_type' => $contractprice->getContractType(),
                'gpo_name' => $contractprice->getGpoName(),
                'name' => $contractprice->getName(),
                'is_dsh' => $contractprice->getDsh(),
                'status' => $contractprice->getStatus(),
                'created_by' =>  $this->authSession->getUser()->getUsername(),
            ]
        );

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('ecomm_exclusiveprice/contractprice/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }

    public function getCustomerGroups()
    {
        $customerGroups = $this->_customerGroup->toOptionArray();
        array_shift($customerGroups);
        // var_dump($customerGroups);
        //  exit;
        return $customerGroups;
    }
}
