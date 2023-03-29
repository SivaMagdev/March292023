<?php
namespace Ecomm\Sap\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Ecomm\Sap\Helper\Sap as SapHelper;

/**
 * Modes source.
 */
class Mode implements ArrayInterface
{
    /**
     * @var SapHelper
     */
    private $helper;

    /**
     * @param SapHelper $helper
     */
    public function __construct(SapHelper $helper)
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
