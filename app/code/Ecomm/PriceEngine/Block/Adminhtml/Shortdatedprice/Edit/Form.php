<?php

namespace Ecomm\PriceEngine\Block\Adminhtml\Shortdatedprice\Edit;

use Ecomm\PriceEngine\Controller\RegistryConstants;

/**
 * Adminhtml customer groups edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

     /**
     * @var \Ecomm\PriceEngine\Model\ShortdatedpriceFactory
     */
    protected $_shortdatedpriceFactory;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Ecomm\PriceEngine\Model\ShortdatedpriceFactory $shortdatedpriceFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Ecomm\PriceEngine\Model\ShortdatedpriceFactory $shortdatedpriceFactory,
        array $data = []
    ) {
        $this->_shortdatedpriceFactory = $shortdatedpriceFactory;
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

        $shortdatedpriceId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SHORTDATEDPRICE_ID);

        if ($shortdatedpriceId === null) {
            $shortdatedprice = $this->_shortdatedpriceFactory->create();
        } else {
            $shortdatedprice = $this->_shortdatedpriceFactory->create()->load($shortdatedpriceId);
        }

        //echo '<pre>test'.print_r($regularprice->getData(), true).'<pre>';

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Price Information')]);

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

        $shortdated_price = $fieldset->addField(
            'shortdated_price',
            'text',
            [
                'name' => 'shortdated_price',
                'label' => __('Short Dated Price'),
                'title' => __('Short Dated Price'),
                'required' => true
            ]
        );

        $inventory = $fieldset->addField(
            'inventory',
            'text',
            [
                'name' => 'inventory',
                'label' => __('Inventory'),
                'title' => __('Inventory'),
                'required' => true
            ]
        );

        $batch = $fieldset->addField(
            'batch',
            'text',
            [
                'name' => 'batch',
                'label' => __('Batch #'),
                'title' => __('Batch #'),
                'required' => true
            ]
        );

        $expiry_date = $fieldset->addField(
            'expiry_date',
            'date',
            [
                'name' => 'expiry_date',
                'label' => __('Start Date'),
                'title' => __('Start Date'),
                'required' => true,
                'singleClick'=> true,
                'date_format' => 'yyyy-MM-dd',
                'time'=>false
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

        /*if ($shortdatedprice->getId() == 0 && $shortdatedprice->getName()) {
            $name->setDisabled(true);
        }*/

        if ($shortdatedprice->getId() !== null) {
            // If edit add id
            $form->addField('shortdated_price_id', 'hidden', ['name' => 'shortdated_price_id', 'value' => $shortdatedprice->getId()]);
        }

        if ($this->_backendSession->getCustomerGroupData()) {
            $form->addValues($this->_backendSession->getCustomerGroupData());
            $this->_backendSession->setCustomerGroupData(null);
        } else {
            // TODO: need to figure out how the DATA can work with forms
            $form->addValues(
                [
                    'id' => $shortdatedprice->getId(),
                    //'thana_id' => $directoryArea->getThanaId(),
                    'name' => $shortdatedprice->getName(),
                    'product_sku' => $shortdatedprice->getProductSku(),
                    'ndc' => $shortdatedprice->getNdc(),
                    'strength_count' => $shortdatedprice->getStrengthCount(),
                    'pack_size' => $shortdatedprice->getPackSize(),
                    'shortdated_price' => $shortdatedprice->getShortdatedPrice(),
                    'inventory' => $shortdatedprice->getInventory(),
                    'batch' => $shortdatedprice->getBatch(),
                    'expiry_date' => $shortdatedprice->getExpiryDate(),
                    'start_date' => $shortdatedprice->getStartDate(),
                    'end_date' => $shortdatedprice->getEndDate(),
                    //'status' => $directoryArea->getStatus(),
                ]
            );
        }

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('ecomm_priceengine/shortdatedprice/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }
}
