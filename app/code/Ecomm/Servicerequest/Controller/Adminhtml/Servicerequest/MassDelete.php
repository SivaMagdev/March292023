<?php

namespace Ecomm\Servicerequest\Controller\Adminhtml\Servicerequest;

use Ecomm\Servicerequest\Model\Servicerequest;

class MassDelete extends MassAction
{
    /**
     * @param Servicerequest $data
     * @return $this
     */
    protected function massAction(Servicerequest $data)
    {
        $this->dataRepository->delete($data);
        return $this;
    }
}
