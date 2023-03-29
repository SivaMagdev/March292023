<?php
/**
 * PwC India
 *
 * @category Magento
 * @package BekaertSWSb2B_RequestCertificate
 * @author PwC India
 * @license GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Ecomm\VideoList\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Description QuoteExtension Table AbstractModel
 */
class VideoList extends AbstractDb
{
    /** @var string Main table name */
    private const MAIN_TABLE = 'drl_video_list';

    /** @var string Main table primary key field name */
    private const ID_FIELD_NAME = 'entity_id';

    /**
     * Define resource model
     */
    protected function _construct(): void
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
