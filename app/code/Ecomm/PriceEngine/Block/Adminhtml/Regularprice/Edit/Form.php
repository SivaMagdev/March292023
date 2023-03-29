<?php

namespace Ecomm\PriceEngine\Block\Adminhtml\Regularprice\Edit;

use Ecomm\PriceEngine\Controller\RegistryConstants;

/**
 * Adminhtml customer groups edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

     /**
     * @var \Ecomm\PriceEngine\Model\RegularPriceFactory
     */
    protected $_regularpriceFactory;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Ecomm\PriceEngine\Model\RegularPriceFactory $regularpriceFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Ecomm\PriceEngine\Model\RegularPriceFactory $regularpriceFactory,
        array $data = []
    ) {
        $this->_regularpriceFactory = $regularpriceFactory;
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

        $regularpriceId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_REGULARPRICE_ID);

        if ($regularpriceId === null) {
            $regularprice = $this->_regularpriceFactory->create();
        } else {
            $regularprice = $this->_regularpriceFactory->create()->load($regularpriceId);
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

        $gpo_name = $fieldset->addField(
            'gpo_name',
            'text',
            [
                'name' => 'gpo_name',
                'label' => __('GPO Name'),
                'title' => __('GPO Name'),
                'required' => false
            ]
        );

        $gpo_price = $fieldset->addField(
            'gpo_price',
            'text',
            [
                'name' => 'gpo_price',
                'label' => __('Gpo Price'),
                'title' => __('Gpo Price'),
                'required' => false
            ]
        );

        $dish_price = $fieldset->addField(
            'dish_price',
            'text',
            [
                'name' => 'dish_price',
                'label' => __('Dish Price'),
                'title' => __('Dish Price'),
                'required' => false
            ]
        );

        $direct_price = $fieldset->addField(
            'direct_price',
            'text',
            [
                'name' => 'direct_price',
                'label' => __('Direct price'),
                'title' => __('Direct Price'),
                'required' => false
            ]
        );

        $gpo_ref = $fieldset->addField(
            'gpo_ref',
            'text',
            [
                'name' => 'gpo_ref',
                'label' => __('GPO #Ref'),
                'title' => __('GPO #Ref'),
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

        if ($regularprice->getId() !== null) {
            // If edit add id
            $form->addField('gpo_price_id', 'hidden', ['name' => 'gpo_price_id', 'value' => $regularprice->getId()]);
        }

        if ($this->_backendSession->getCustomerGroupData()) {
            $form->addValues($this->_backendSession->getCustomerGroupData());
            $this->_backendSession->setCustomerGroupData(null);
        } else {
            // TODO: need to figure out how the DATA can work with forms
            $form->addValues(
                [
                    'id' => $regularprice->getId(),
                    //'thana_id' => $directoryArea->getThanaId(),
                    'name' => $regularprice->getName(),
                    'product_sku' => $regularprice->getProductSku(),
                    'ndc' => $regularprice->getNdc(),
                    'strength_count' => $regularprice->getStrengthCount(),
                    'pack_size' => $regularprice->getPackSize(),
                    'gpo_name' => $regularprice->getGpoName(),
                    'gpo_price' => $regularprice->getGpoPrice(),
                    'dish_price' => $regularprice->getDishPrice(),
                    'direct_price' => $regularprice->getDirectPrice(),
                    'start_date' => $regularprice->getStartDate(),
                    'end_date' => $regularprice->getEndDate(),
                    'gpo_ref' => $regularprice->getGpoRef(),
                    //'status' => $directoryArea->getStatus(),
                ]
            );
        }

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('ecomm_priceengine/regularprice/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }
}
