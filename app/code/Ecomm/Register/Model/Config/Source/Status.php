<?php
namespace Ecomm\Register\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Ecomm\Register\Helper\Medpro as MedproHelper;

/**
 * Status source.
 */
class Status implements ArrayInterface
{
    /**
     * @var MedproHelper
     */
    private $helper;

    /**
     * @param MedproHelper $helper
     */
    public function __construct(MedproHelper $helper)
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
        return $this->helper->getStatuses();
    }
}
