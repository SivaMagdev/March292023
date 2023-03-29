<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Cart Reservation v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\CartReservation\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Plumrocket\CartReservation\Helper\Product as ProductHelper;

class Uninstall extends \Plumrocket\Base\Setup\AbstractUninstall
{
    /**
     * Config section id
     *
     * @var string
     */
    protected $_configSectionId = 'prcr';

    /**
     * Pathes to files
     *
     * @var array
     */
    protected $_pathes = ['/app/code/Plumrocket/CartReservation'];

    /**
     * Attributes
     *
     * @var array
     */
    protected $_attributes = [
        Category::ENTITY => [ProductHelper::ATTRIBUTE_CODE],
        Product::ENTITY => [ProductHelper::ATTRIBUTE_CODE],
    ];

    /**
     * Tables
     *
     * @var array
     */
    protected $_tables = [];
}
