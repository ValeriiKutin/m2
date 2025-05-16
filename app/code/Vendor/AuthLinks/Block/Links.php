<?php
namespace Vendor\AuthLinks\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

class Links extends Template
{
    protected $customerSession;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getLogoutUrl()
    {
        return $this->getUrl('customer/account/logout');
    }

    public function getLoginUrl()
    {
        return $this->getUrl('customer/account/login');
    }

    public function getCustomerName()
    {
        return $this->customerSession->getCustomer()->getName();
    }
}
