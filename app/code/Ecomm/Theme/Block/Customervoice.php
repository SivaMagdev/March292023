<?php
namespace Ecomm\Theme\Block;

class Customervoice extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context);
	}
}