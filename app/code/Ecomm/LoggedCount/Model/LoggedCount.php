<?php
/**
 * @category   Ecomm
 * @package    Ecomm_LoggedCount
 * @author     pwc@gmail.com
 */

namespace Ecomm\LoggedCount\Model;

use Magento\Framework\Model\AbstractModel;

class LoggedCount extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ecomm\LoggedCount\Model\ResourceModel\LoggedCount');
    }
}
