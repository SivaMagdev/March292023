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
namespace Ecomm\Resources\Controller\Adminhtml\Resourcescategory;

use Ecomm\Resources\Controller\Adminhtml\Resourcescategory;

class Add extends Resourcescategory
{
    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}