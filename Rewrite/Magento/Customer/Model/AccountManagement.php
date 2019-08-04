<?php


namespace Xigen\UsernameLogin\Rewrite\Magento\Customer\Model;

use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

/**
 * Undocumented class
 */
class AccountManagement extends \Magento\Customer\Model\AccountManagement
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * AccountManagement constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface $eventManager
     * @param CustomerFactory $customerFactory
     * @param CustomerCollectionFactory $customerCollectionFactory
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $eventManager,
        CustomerFactory $customerFactory,
        CustomerCollectionFactory $customerCollectionFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
        $customerId = $this->getCustomerByUsername($username);
        if ($customerId) {
            try {
                $customer = $this->customerRepository->getById($customerId);
            } catch (NoSuchEntityException $e) {
                throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
            }
        } else {
            try {
                $customer = $this->customerRepository->get($username);
            } catch (NoSuchEntityException $e) {
                throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
            }
            $customerId = $customer->getId();
        }

        if ($this->getAuthentication()->isLocked($customerId)) {
            throw new UserLockedException(__('The account is locked.'));
        }
        try {
            $this->getAuthentication()->authenticate($customerId, $password);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        if ($customer->getConfirmation() && $this->isConfirmationRequired($customer)) {
            throw new EmailNotConfirmedException(__('This account is not confirmed.'));
        }

        $customerModel = $this->customerFactory->create()->updateData($customer);
        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }

    /**
     * Get authentication
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {
        if (!($this->authentication instanceof AuthenticationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * @param null $username
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerByUsername($username = null)
    {
        if ($username) {
            $collection = $this->customerCollectionFactory
                ->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('username', ['eq' => $username]);
            foreach ($collection as $customer) {
                return $customer->getId();
            }
        }
        return false;
    }
}
