<?php

namespace Ecomm\Register\Controller\Domain;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

/**
 * Class Validate
 */
class Validate extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    private $customerFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->customerFactory = $customerFactory;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        //echo $this->getRequest()->getParam('company_email_registered');

        $resultJson->setData([
            'company_email_registered' => $this->isCompanyEmailValid($this->getRequest()->getParam('company_email_registered')),
            'customer_email' => $this->isCustomerEmailValid($this->getRequest()->getParam('customer_email')),
            'domain_exist' => $this->isEmailDomainExist($this->getRequest()->getParam('company_email_registered'))
        ]);

        return $resultJson;
    }

    /**
     * Is company email valid
     *
     * @param string $email
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isCompanyEmailValid($email)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CompanyInterface::COMPANY_EMAIL, $email)
            ->create();
        return !$this->companyRepository->getList($searchCriteria)->getTotalCount();
    }

    /**
     * Is customer email valid
     *
     * @param string $email
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isCustomerEmailValid($email)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CustomerInterface::EMAIL, $email)
            ->create();
        return !$this->customerRepository->getList($searchCriteria)->getTotalCount();
    }

    /**
     * Is customer email valid
     *
     * @param string $email
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isEmailDomainExist($email)
    {

        $email_data = explode("@", $email);

        //echo $email_data[1];

        $collection = $this->customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("company_email_registered", array("like" => '%'.$email_data[1]))
            ->load();

        //echo '<pre>'.print_r($collection->getData(), true).'</pre>';

        return $collection->count();
    }
}
