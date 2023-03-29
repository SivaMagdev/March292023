<?php

namespace Ecomm\PriceEngine\Block\Adminhtml\Stock\Edit;

use Ecomm\PriceEngine\Controller\RegistryConstants;

/**
 * Adminhtml customer groups edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

     /**
     * @var \Ecomm\PriceEngine\Model\StockFactory
     */
    protected $_stockFactory;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Ecomm\PriceEngine\Model\StockFactory $stockFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Ecomm\PriceEngine\Model\StockFactory $stockFactory,
        array $data = []
    ) {
        $this->_stockFactory = $stockFactory;
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

        $stockId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_STOCK_ID);

        if ($stockId === null) {
            $stock_data = $this->_stockFactory->create();
        } else {
            $stock_data = $this->_stockFactory->create()->load($stockId);
        }

        //echo '<pre>test'.print_r($stock_data->getData(), true).'<pre>';

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

        $stock = $fieldset->addField(
            'stock',
            'text',
            [
                'name' => 'stock',
                'label' => __('Inventory'),
                'title' => __('Inventory'),
                'required' => true
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

        /*if ($stock_data->getId() == 0 && $stock_data->getName()) {
            $name->setDisabled(true);
        }*/

        if ($stock_data->getId() !== null) {
            // If edit add id
            $form->addField('stock_id', 'hidden', ['name' => 'stock_id', 'value' => $stock_data->getId()]);
        }

        if ($this->_backendSession->getCustomerGroupData()) {
            $form->addValues($this->_backendSession->getCustomerGroupData());
            $this->_backendSession->setCustomerGroupData(null);
        } else {
            // TODO: need to figure out how the DATA can work with forms
            //echo $stock->getProductSku();
            $form->addValues(
                [
                    'id' => $stock_data->getId(),
                    'product_sku' => $stock_data->getProductSku(),
                    'stock' => $stock_data->getStock(),
                    'start_date' => $stock_data->getStartDate(),
                    'end_date' => $stock_data->getEndDate(),
                    //'status' => $directoryArea->getStatus(),
                ]
            );
        }

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('ecomm_priceengine/stock/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }
}
