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
namespace Ecomm\News\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Ecomm\News\Api\News\NewsInterface;

interface NewsRepositoryInterface
{

    /**
     * @param NewsInterface $data
     * @return mixed
     */
    public function save(NewsInterface $data);


    /**
     * @param $dataId
     * @return mixed
     */
    public function getById($id);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Ecomm\News\Api\News\NewsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param NewsInterface $data
     * @return mixed
     */
    public function delete(NewsInterface $data);

    /**
     * @param $dataId
     * @return mixed
     */
    public function deleteById($id);
}
