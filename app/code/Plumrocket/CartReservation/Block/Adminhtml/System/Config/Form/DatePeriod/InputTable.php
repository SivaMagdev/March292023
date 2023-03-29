<?php
/**
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\CartReservation\Block\Adminhtml\System\Config\Form\DatePeriod;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Exception\LocalizedException;
use Plumrocket\CartReservation\Block\Adminhtml\System\Config\Form\DatePeriod\InputTable\Column;

/**
 * @since 2.4.0
 */
class InputTable extends Extended implements RendererInterface
{

    private const HIDDEN_ELEMENT_CLASS = 'hidden-input-table';

    /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
    protected $_element;

    /** @var string  */
    protected $_containerFieldId;

    /** @var string */
    protected $_rowKey;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Backend\Helper\Data              $backendHelper
     * @param \Magento\Framework\Data\CollectionFactory $collectionFactory
     * @param \Magento\Framework\DataObjectFactory      $dataObjectFactory
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectFactory = $dataObjectFactory;

        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function _construct()
    {
        parent::_construct();
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setMessageBlockVisibility(false);
    }

    /**
     * @param string                              $columnId
     * @param array|\Magento\Framework\DataObject $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addColumn($columnId, $column): self
    {
        if (is_array($column)) {
            $column['sortable'] = false;
            $this->getColumnSet()->setChild(
                $columnId,
                $this->getLayout()
                    ->createBlock(Column::class)
                ->setData($column)
                ->setId($columnId)
                ->setGrid($this)
            );
            $this->getColumnSet()->getChildBlock($columnId)->setGrid($this);
        } else {
            throw new LocalizedException(__('Please correct the column format and try again.'));
        }

        $this->_lastColumnId = $columnId;
        return $this;
    }

    /**
     * @return bool
     */
    public function canDisplayContainer(): bool
    {
        return false;
    }

    /**
     * @return \Magento\Backend\Block\Widget|\Plumrocket\CartReservation\Block\Adminhtml\System\Config\Form\DatePeriod\InputTable
     */
    protected function _prepareLayout()
    {
        return \Magento\Backend\Block\Widget::_prepareLayout();
    }

    public function setArray($array): InputTable
    {
        $collection = $this->collectionFactory->create();
        $i = 1;
        foreach ($array as $item) {
            if (! $item instanceof \Magento\Framework\DataObject) {
                $item = $this->dataObjectFactory->create(['data' => $item]);
            }

            if (! $item->getId()) {
                $item->setId($i);
            }

            $collection->addItem($item);
            $i++;
        }
        $this->setCollection($collection);
        return $this;
    }

    public function getRowKey(): string
    {
        return $this->_rowKey;
    }

    public function setRowKey($key): InputTable
    {
        $this->_rowKey = $key;
        return $this;
    }

    public function getContainerFieldId(): string
    {
        return $this->_containerFieldId;
    }

    public function setConatinerFieldId($name): InputTable
    {
        $this->_containerFieldId = $name;
        return $this;
    }

    protected function _toHtml(): string
    {
        $html = parent::_toHtml();
        $html = preg_replace(
            '/(\s+class\s*=\s*["\'](?:\s*|[^"\']*\s+)messages)((?:\s*|\s+[^"\']*)["\'])/isU',
            '$1 ' . self::HIDDEN_ELEMENT_CLASS . ' $2',
            $html
        );

        $html = str_replace(
            '<div class="admin__data-grid-wrap',
            '<div id="' . $this->getHtmlId() . '_wrap" class="admin__data-grid-wrap',
            $html
        );

        $html .= $this->_getCss();
        return $html;
    }

    /**
     * @return string
     */
    protected function _getCss(): string
    {
        $id = '#' . $this->getHtmlId() . '_wrap';
        return
            "<style>
                .messages." . self::HIDDEN_ELEMENT_CLASS . " {display: none}
                $id {
                    margin-bottom: 0;
                    padding-bottom: 0;
                    padding-top: 0;
                }
                $id td {
                    padding: 1rem;
                    vertical-align: middle;
                }
                $id td input.checkbox[disabled] {
                    display: none;
                }
                $id tr.not-active td,
                $id tr.not-active input.input-text {
                    color: #999999;
                }
            </style>";
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $this->setElement($element);
        return
            '<tr>
                <td class="label">' . $element->getLabelHtml() . '</td>
                <td class="value">' . $this->toHtml() . '</td>
            </tr>';
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return $this
     */
    public function setElement(AbstractElement $element): self
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getElement(): AbstractElement
    {
        return $this->_element;
    }
}
