<?php

declare(strict_types=1);

namespace Ecomm\PriceEngine\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Escaper;

/**
 * Class RegularPriceActions
 */
class RegularPriceActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'ecomm_priceengine/regularprice/edit';
    const URL_PATH_DELETE = 'ecomm_priceengine/regularprice/delete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
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
                if (isset($item['gpo_price_id'])) {
                    $title = $this->escaper->escapeHtml($item['name']);
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'id' => $item['gpo_price_id']
                                ]
                            ),
                            'label' => __('Edit'),
                            '__disableTmpl' => true
                        ],
                    ];

                    $item[$this->getData('name')]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_DELETE,
                            [
                                'id' => $item['gpo_price_id']
                            ]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete %1', $this->escaper->escapeJs($title)),
                            'message' => __(
                                'Are you sure you want to delete a %1 record?',
                                $this->escaper->escapeJs($title)
                            )
                        ],
                        'post' => true,
                        '__disableTmpl' => true
                    ];
                }
            }
        }

        return $dataSource;
    }
}
