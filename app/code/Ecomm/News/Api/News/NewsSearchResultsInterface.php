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
 * @package     Ecomm_News
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\News\Api\News;

use Magento\Framework\Api\SearchResultsInterface;

interface NewsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get data list.
     *
     * @return \Ecomm\News\Api\News\NewsInterface[]
     */
    public function getItems();

    /**
     * Set data list.
     *
     * @param \Ecomm\News\Api\News\NewsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
