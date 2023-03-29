<?php
/**
 * PwC India
 *
 * @category Magento
 * @package BekaertSWSb2B_RequestCertificate
 * @author PwC India
 * @license GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\VideoList\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Encryption\EncryptorInterface;

class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public const URL_PATH_EDIT = 'drlvideo/index/videoform';

    /**
     * @var UrlInterface
     */
    protected UrlInterface $urlBuilder;

    /**
     * constructor
     *
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
                $item[$this->getData('name')] = [
                    'edit' => [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_EDIT,
                            [
                                'entity_id' => $item['entity_id']
                            ]
                        ),
                        'label' => __('Edit')
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
