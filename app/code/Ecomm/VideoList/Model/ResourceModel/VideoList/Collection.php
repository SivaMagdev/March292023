<?php
/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_VideoList
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Ecomm\VideoList\Model\ResourceModel\VideoList;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ecomm\VideoList\Model\VideoList as Model;
use Ecomm\VideoList\Model\ResourceModel\VideoList as ResourceModel;

/**
 * Description QuoteExtension Database Connection
 */
class Collection extends AbstractCollection
{
    /**
     * Connection Resource
     */
    protected function _construct(): void
    {
        $this->_init(
            Model::class,
            ResourceModel::class
        );
    }
}
