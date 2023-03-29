<?php
namespace Ecomm\CustomerGrid\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class LastLogin extends Column
{
    protected $logger;

    protected $timezone;
    /**
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        \Magento\Customer\Model\Logger $logger,
        TimezoneInterface $timezone
    ) {

        $this->logger = $logger;
        $this->timezone = $timezone;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                ///$item[$this->getData('name')] = $this->logger->get($item['entity_id'])->getLastLoginAt();

                $date = $this->logger->get($item['entity_id'])->getLastLoginAt();

                if ($date) {
                    $item[$this->getData('name')] = $this->timezone->date(new \DateTime($date))->format('Y-m-d H:i:s');
                } else {
                    $item[$this->getData('name')] = __('Never');
                }
            }
        }
        return $dataSource;
    }
}