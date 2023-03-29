<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * PWC does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PWC does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    PWC
 * @package     Ecomm_Resources
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Resources\Model\Resources\Source;

class Category implements \Magento\Framework\Data\OptionSourceInterface
{
    public function __construct(
        \Ecomm\Resources\Model\ResourceModel\Resourcescategory\Collection $resourcescategory
    ) {
        $this->resourcescategory = $resourcescategory;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];

        foreach ($this->resourcescategory->getData() as $category) {
            if($category['status']){
                $options[] = [
                    'label' => $category['category'],
                    'value' => $category['id'],
                ];  
            }
        }
        return $options;
    }
}
?>