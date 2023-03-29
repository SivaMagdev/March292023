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
 * @package     Ecomm_Rewards
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Rewards\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Ecomm\Rewards\Api\Rewards\RewardsInterface;

interface RewardsRepositoryInterface
{

    /**
     * @param RewardsInterface $data
     * @return mixed
     */
    public function save(RewardsInterface $data);


    /**
     * @param $dataId
     * @return mixed
     */
    public function getById($id);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Ecomm\Rewards\Api\Rewards\RewardsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param RewardsInterface $data
     * @return mixed
     */
    public function delete(RewardsInterface $data);

    /**
     * @param $dataId
     * @return mixed
     */
    public function deleteById($id);
}
