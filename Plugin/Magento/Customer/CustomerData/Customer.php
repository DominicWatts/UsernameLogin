<?php

namespace Xigen\UsernameLogin\Plugin\Magento\Customer\CustomerData;

/**
 * Class Customer
 * @package Xigen\CustomerLogin\Plugin\Magento\Customer\CustomerData
 */
class Customer
{
    /**
     * @var \Magento\Customer\Model\Session\Proxy
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * Customer constructor.
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     */
    // phpcs:disable
    public function __construct(
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }
    // phpcs:enable
    /**
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param $result
     * @return mixed
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        $result['username'] = '';
        if ($this->customerSession->isLoggedIn() && $this->customerSession->getCustomerId()) {
            $customer = $this->getById($this->customerSession->getCustomerId());
            if ($username = $customer->getCustomAttribute('username')) {
                $result['username'] = $username->getValue() ?: null;
            }
        }
        return $result;
    }

    /**
     * Get customer by Id.
     * @param int $customerId
     * @return \Magento\Customer\Model\Data\Customer
     */
    public function getById($customerId)
    {
        try {
            return $this->customerRepositoryInterface->getById($customerId);
        } catch (\Exception $e) {
            return false;
        }
    }
}
