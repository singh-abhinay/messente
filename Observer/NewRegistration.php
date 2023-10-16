<?php

namespace Abhinay\Messente\Observer;

/**
 * Class NewRegistration
 * @package Abhinay\Messente\Observer
 */
class NewRegistration implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Abhinay\Messente\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * NewRegistration constructor.
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerInstanceRepository
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Abhinay\Messente\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerInstanceRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Abhinay\Messente\Helper\Data $helper
    )
    {
        $this->request = $request;
        $this->customerRepository = $customerInstanceRepository;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $postData = (array)$this->request->getPost();
        $customerInstance = $observer->getEvent()->getCustomer();
        if ((array_key_exists('mobile_number', $postData)) || (array_key_exists('telephone', $postData))) {
            if (!empty($postData['mobile_number'])) {
                $this->saveCustomerNumber($postData['mobile_number'], $customerInstance);
            } else {
                $this->saveCustomerNumber($postData['telephone'], $customerInstance);
            }
        }
        if (($this->helper->getMessenteSmsStatus()) && ($this->helper->getMessenteSmsStatus() != 0)) {
            if ($this->helper->getNewRegistrationStatus() != 0) {
                $messageCustomer = $this->helper->getRegistrationMessage($customerInstance);
                $mobile = '';
                if ((array_key_exists('mobile_number', $postData)) && (!empty($postData['mobile_number']))) {
                    $mobile = $postData['mobile_number'];
                } else {
                    $mobile = $postData['telephone'];
                }
                if (!empty($mobile)) {
                    $dialCode = $this->helper->getCountryCode($postData['country_id']);
                    $this->helper->sendSms($dialCode . $mobile, $messageCustomer);
                }
            }
            if ($this->helper->getRegistrationMsgStatusAdmin() != 0) {
                $numberAdmin = $this->helper->getAdminNumber();
                $messageAdmin = $this->helper->getRegistrationMessageAdmin($customerInstance);
                $this->helper->sendSms($numberAdmin, $messageAdmin);
            }
        }
    }

    /**
     * @param $mobileNumber
     * @param $customerInstance
     */
    public function saveCustomerNumber($mobileNumber, $customerInstance)
    {
        $id = $this->helper->getSiteWebsiteId();
        $customer = $this->customerRepository->get($customerInstance->getEmail(), $id);
        $customer->setCustomAttribute('mobile', $mobileNumber);
        $this->customerRepository->save($customer);
    }
}
