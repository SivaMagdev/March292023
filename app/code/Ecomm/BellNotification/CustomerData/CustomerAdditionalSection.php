<?php
namespace Ecomm\BellNotification\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Helper\View;

class CustomerAdditionalSection implements SectionSourceInterface
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

   /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param View $customerViewHelper
     */

    public function __construct(
        CurrentCustomer $currentCustomer,
        CompanyRepositoryInterface $companyRepository,
        CompanyManagementInterface $companyManagement,
        \Magento\Framework\App\Http\Context $httpContext,
    	\Ecomm\BellNotification\Helper\BellNotification $bellNotificationHelper
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->companyRepository = $companyRepository;
        $this->companyManagement = $companyManagement;
        $this->httpContext = $httpContext;
        $this->bellNotificationHelper = $bellNotificationHelper;
	}

	/**
     * {@inheritdoc}
     */
    public function getSectionData()
    {

    	if (!$this->httpContext->getValue('customer_id')) {
            return [];
        }
        /*try {
            $companyId = $this->companyManagement->getByCustomerId($this->httpContext->getValue('customer_id'))->getId();
            $company_info = $this->companyRepository->get($companyId);
            $param = ['companyname'=> $company_info->getCompanyName()];
            return $param;
        } catch (NoSuchEntityException $noSuchEntityException) {
            $param = ['companyname'=> ''];
            return [];
        }*/

        return [];
    }
}