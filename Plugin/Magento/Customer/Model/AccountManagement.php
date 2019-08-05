<?php

namespace Xigen\UsernameLogin\Plugin\Magento\Customer\Model;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

/**
 * Class AccountManagement
 * @package Xigen\UsernameLogin\Plugin\Magento\Customer\Model
 */
class AccountManagement
{
    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * AccountManagement constructor.
     * @param CustomerCollectionFactory $customerCollectionFactory
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param $customerEmail
     * @param null $websiteId
     * @return array
     */
    public function beforeIsEmailAvailable(\Magento\Customer\Model\AccountManagement $subject, $customerEmail, $websiteId = null)
    {
        $checkUsername = $this->getCustomerByUsername($customerEmail);
        if ($checkUsername && $checkUsername->getSize()) {
            $customer = $checkUsername->getFirstItem();
            $customerEmail = $customer->getEmail();
        }

        if (!\Zend_Validate::is($customerEmail, 'EmailAddress')) {
            $error = true;
        }

        return [$customerEmail, $websiteId];
    }

    /**
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param $username
     * @param $password
     * @return array
     */
    public function beforeAuthenticate(\Magento\Customer\Model\AccountManagement $subject, $username, $password)
    {
        $checkUsername = $this->getCustomerByUsername($username);
        if ($checkUsername && $checkUsername->getSize()) {
            $customer = $checkUsername->getFirstItem();
            $username = $customer->getEmail();
        }
        return [$username, $password];
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
            return $collection;
        }
        return false;
    }
}
