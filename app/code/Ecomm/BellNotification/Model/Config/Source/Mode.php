<?php
namespace Ecomm\BellNotification\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Ecomm\BellNotification\Helper\PushNotification as Helper;

/**
 * Modes source.
 */
class Mode implements ArrayInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Return list of available modes
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->helper->getModes();
    }
}
