<?php 
namespace Ecomm\HinEligibilityCheck\Block\Adminhtml\Edit\Address;

use Magento\Customer\Block\Adminhtml\Edit\Address\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class HinEligibilityCheck extends GenericButton implements ButtonProviderInterface
{   

    /**
     * @var GenericButton
     */
    private $genericButton;

    /**
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        GenericButton $genericButton
    ) {
        $this->genericButton = $genericButton;
    }



    public function getButtonData()
    {
        $addressId = $this->genericButton->getAddressId();

        if ($addressId) {
            return [
                'label' => __('Check HIN Eligibility'),
                'on_click' => sprintf("location.href = '%s';", $this->getCustomUrl()),
                'sort_order' => 15
            ];
        }
    }

    /**
     * URL getter
     *
     * @return string
     */
    public function getCustomUrl()
    {
        return $this->genericButton->getUrl('custom/index/index', ['address_id' => $this->genericButton->getAddressId()]);
    }
}