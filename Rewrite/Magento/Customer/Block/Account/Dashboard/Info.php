<?php

namespace Xigen\UsernameLogin\Rewrite\Magento\Customer\Block\Account\Dashboard;

/**
 * Info Block class
 */
class Info extends \Magento\Customer\Block\Account\Dashboard\Info
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $helperView,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->logger = $logger;
        parent::__construct($context, $currentCustomer, $subscriberFactory, $helperView, $data);
    }
    /**
     * Get customer account manager
     * @return string
     */
    public function getUsername()
    {
        if ($customer = $this->getCustomer()) {
            if ($accountManager = $customer->getCustomAttribute('username')) {
                return $accountManager->getValue() ?: null;
            }
        }
        return false;
    }
}
