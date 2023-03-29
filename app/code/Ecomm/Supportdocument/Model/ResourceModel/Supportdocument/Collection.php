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
 * @package     Ecomm_Supportdocument
 * @version     1.2
 * @author      PWC Team
 *
 */
namespace Ecomm\Supportdocument\Model\ResourceModel\Supportdocument;

/**
 * This class contains order model collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

	/**
     * @var string
     * @codingStandardsIgnoreStart
     */
    protected $_idFieldName = 'id';
    /**
     * Define model & resource model
     */
    protected function _construct() {
        $this->_init ( 'Ecomm\Supportdocument\Model\Supportdocument', 'Ecomm\Supportdocument\Model\ResourceModel\Supportdocument' );
    }
}