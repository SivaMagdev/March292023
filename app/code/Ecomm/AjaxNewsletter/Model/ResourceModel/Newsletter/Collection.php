<?php
namespace Ecomm\AjaxNewsletter\Model\ResourceModel\Newsletter;

class Collection extends \Magento\Newsletter\Model\ResourceModel\Subscriber\Grid\Collection
{

    protected function _initSelect()
    {
        //die("fs");
        parent::_initSelect();
        $this->addSubscriberTypeField();
        $this->_map['fields']['last_modified'] = 'main_table.change_status_at';
        return $this;
    }
}