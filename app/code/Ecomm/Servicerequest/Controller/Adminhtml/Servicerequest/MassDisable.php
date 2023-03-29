<?php

namespace Ecomm\Servicerequest\Controller\Adminhtml\Servicerequest;

use Ecomm\Servicerequest\Model\Servicerequest;

class MassDisable extends MassAction
{
    /**
     * @param Servicerequest $data
     * @return $this
     */
    protected function massAction(Servicerequest $data)
    {
        $data->setStatus(false);
        $this->dataRepository->save($data);
        return $this;
    }
}
