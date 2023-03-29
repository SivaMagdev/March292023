<?php

namespace Ecomm\Servicerequest\Controller\Adminhtml\Servicerequest;

use Ecomm\Servicerequest\Model\Servicerequest;

class MassEnable extends MassAction
{
    /**
     * @param Servicerequest $data
     * @return $this
     */
    protected function massAction(Servicerequest $data)
    {
        $data->setStatus(true);
        $this->dataRepository->save($data);
        return $this;
    }
}
