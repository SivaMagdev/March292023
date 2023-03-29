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
    protected $_exclusivepriceFactory;


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
        \Ecomm\PriceEngine\Model\ExclusivePriceFactory $exclusivepriceFactory,
        array $data = []
    ) {
        $this->_exclusivepriceFactory = $exclusivepriceFactory;
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

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $exclusivepriceId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_EXCLUSIVEPRICE_ID);

        if ($exclusivepriceId === null) {
            $exclusiveprice = $this->_exclusivepriceFactory->create();
        } else {
            $exclusiveprice = $this->_exclusivepriceFactory->create()->load($exclusivepriceId);
        }

        //echo '<pre>test'.print_r($regularprice->getData(), true).'<pre>';

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Price Information')]);

        $product_sku = $fieldset->addField(
            'product_sku',
            'text',
            [
                'name' => 'product_sku',
                'label' => __('SKU'),
                'title' => __('SKU'),
                'required' => true
            ]
        );

        $ndc = $fieldset->addField(
            'ndc',
            'text',
            [
                'name' => 'ndc',
                'label' => __('NDC'),
                'title' => __('NDC'),
                'required' => false
            ]
        );

        $name = $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Product Name'),
                'title' => __('Product Name'),
                'required' => true
            ]
        );

        $strength_count = $fieldset->addField(
            'strength_count',
            'text',
            [
                'name' => 'strength_count',
                'label' => __('Strength & Count'),
                'title' => __('NDC'),
                'required' => false
            ]
        );

        $pack_size = $fieldset->addField(
            'pack_size',
            'text',
            [
                'name' => 'pack_size',
                'label' => __('Pack Size'),
                'title' => __('Pack Size'),
                'required' => false
            ]
        );

        $customer_id = $fieldset->addField(
            'customer_id',
            'text',
            [
                'name' => 'customer_id',
                'label' => __('SAP Customer ID'),
                'title' => __('SAP Customer ID'),
                'required' => false
            ]
        );

        $price = $fieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Price'),
                'title' => __('Price'),
                'required' => false
            ]
        );

        $contract_ref = $fieldset->addField(
            'contract_ref',
            'text',
            [
                'name' => 'contract_ref',
                'label' => __('Contract #Ref'),
                'title' => __('Contract #Ref'),
                'required' => false
            ]
        );

        $start_date = $fieldset->addField(
            'start_date',
            'date',
            [
                'name' => 'start_date',
                'label' => __('Start Date'),
                'title' => __('Start Date'),
                'required' => true,
                'singleClick'=> true,
                'date_format' => 'yyyy-MM-dd',
                'time'=>false
            ]
        );

        $end_date = $fieldset->addField(
            'end_date',
            'date',
            [
                'name' => 'end_date',
                'label' => __('End Date'),
                'title' => __('End Date'),
                'required' => true,
                'singleClick'=> true,
                'date_format' => 'yyyy-MM-dd',
                'time'=>false
            ]
        );

        /*$_thanas = $this->_thanaFactory->create()->getCollection();
        //$_states = $_states->addFieldToFilter('country_id', 'BD');

        $_thanas_options = [];
        $_thanas_options[''] = __('Please Select');

        if (count($_thanas->getData()) > 0) {
            foreach ($_thanas as $_thana) {
                $_thanas_options[$_thana['thana_id']] = $_thana['name'];
            }
        }


        $thana = $fieldset->addField(
            'thana_id',
            'select',
            [
                'name'      => 'thana_id',
                'label'     => __('Thana'),
                'title' => __('Thana'),
                'values'   => $_thanas_options,
                'required'     => true
            ]
        );*/

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

        if ($exclusiveprice->getId() !== null) {
            // If edit add id
            $form->addField('exclusive_price_id', 'hidden', ['name' => 'exclusive_price_id', 'value' => $exclusiveprice->getId()]);
        }

        if ($this->_backendSession->getCustomerGroupData()) {
            $form->addValues($this->_backendSession->getCustomerGroupData());
            $this->_backendSession->setCustomerGroupData(null);
        } else {
            // TODO: need to figure out how the DATA can work with forms
            $form->addValues(
                [
                    'id' => $exclusiveprice->getId(),
                    'name' => $exclusiveprice->getName(),
                    'product_sku' => $exclusiveprice->getProductSku(),
                    'ndc' => $exclusiveprice->getNdc(),
                    'strength_count' => $exclusiveprice->getStrengthCount(),
                    'pack_size' => $exclusiveprice->getPackSize(),
                    'customer_id' => $exclusiveprice->getCustomerId(),
                    'price' => $exclusiveprice->getPrice(),
                    'start_date' => $exclusiveprice->getStartDate(),
                    'end_date' => $exclusiveprice->getEndDate(),
                    'contract_ref' => $exclusiveprice->getContractRef(),
                ]
            );
        }

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('ecomm_priceengine/exclusiveprice/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }
}
