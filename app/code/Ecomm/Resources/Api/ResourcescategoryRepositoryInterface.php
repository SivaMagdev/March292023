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
namespace Ecomm\Resources\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Ecomm\Resources\Api\Resourcescategory\ResourcescategoryInterface;

interface ResourcescategoryRepositoryInterface
{

    /**
     * @param ResourcescategoryInterface $data
     * @return mixed
     */
    public function save(ResourcescategoryInterface $data);


    /**
     * @param $dataId
     * @return mixed
     */
    public function getById($id);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Ecomm\Resources\Api\Resourcescategory\ResourcescategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param ResourcescategoryInterface $data
     * @return mixed
     */
    public function delete(ResourcescategoryInterface $data);

    /**
     * @param $dataId
     * @return mixed
     */
    public function deleteById($id);
}
