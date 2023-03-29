<?php
namespace Ecomm\Globaldeclaration\Model\ResourceModel\Order\Shipment\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
	protected $helper;

	public function __construct(
	EntityFactory $entityFactory,
	Logger $logger,
	FetchStrategy $fetchStrategy,
	EventManager $eventManager,
	$mainTable = 'sales_shipment_grid',
	$resourceModel = \Magento\Sales\Model\ResourceModel\Order\Shipment::class
	)
	{
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
	}

	protected function _renderFiltersBefore()
	{
		$joinTable = $this->getTable('sales_shipment_track');
		$this->getSelect()->joinLeft($joinTable, 'main_table.entity_id = sales_shipment_track.parent_id', ['track_number']);
		$this->getSelect()->group('sales_shipment_track.parent_id');
		parent::_renderFiltersBefore();
	}
}
