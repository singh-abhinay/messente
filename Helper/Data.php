<?php

namespace Abhinay\Messente\Helper;

use Messente\Api\Api\OmnimessageApi;
use Messente\Api\Model\Omnimessage;
use Messente\Api\Configuration;
use Messente\Api\Model\SMS;

/**
 * Class Data
 * @package Abhinay\Messente\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Abhinay\Core\Logger\Logger
     */
    protected $customLogger;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Abhinay\Core\Logger\Logger $customLogger
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Order $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Abhinay\Core\Logger\Logger $customLogger
    )
    {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->order = $order;
        $this->scopeConfig = $scopeConfig;
        $this->customLogger = $customLogger;
    }

    /**
     * @return mixed
     */
    public function getMessenteSmsStatus()
    {
        return $this->scopeConfig->getValue('messentesms/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getNewRegistrationStatus()
    {
        return $this->scopeConfig->getValue('messentesms/registration/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $customerInstance
     * @return array
     */
    public function getRegistrationDetail($customerInstance)
    {
        $details = [
            $customerInstance->getEmail(),
            $customerInstance->getFirstname() . ' ' . $customerInstance->getLastname(),
            $customerInstance->getFirstname(),
            $customerInstance->getMiddlename(),
            $customerInstance->getLastname()
        ];
        return $details;
    }

    /**
     * @param $customerInstance
     * @return mixed
     */
    public function getRegistrationMessage($customerInstance)
    {
        $variables = ['{{email}}', '{{name}}', '{{firstname}}', '{{middlename}}', '{{lastname}}'];
        $customerDetail = $this->getRegistrationDetail($customerInstance);
        $registrationMessage = $this->scopeConfig->getValue('messentesms/registration/message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return str_replace($variables, $customerDetail, $registrationMessage);
    }

    /**
     * @return mixed
     */
    public function getRegistrationMsgStatusAdmin()
    {
        return $this->scopeConfig->getValue('messentesms/registration/admin_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $customerInstance
     * @return mixed
     */
    public function getRegistrationMessageAdmin($customerInstance)
    {
        $variableList = ['{{email}}', '{{name}}', '{{firstname}}', '{{middlename}}', '{{lastname}}'];
        $customerDetail = $this->getRegistrationDetail($customerInstance);
        $registrationMessage = $this->scopeConfig->getValue('messentesms/registration/admin_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return str_replace($variableList, $customerDetail, $registrationMessage);
    }

    /**
     * @return mixed
     */
    public function getAdminNumber()
    {
        return $this->scopeConfig->getValue('messentesms/registration/admin_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getNewOrderNotificationCustomer()
    {
        return $this->scopeConfig->getValue('messentesms/order/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $orderInstance
     * @return mixed
     */
    public function getNewOrderMessageCustomer($orderInstance)
    {
        $dynamicVar = ['{{order_id}}', '{{email}}', '{{name}}', '{{firstname}}', '{{middlename}}', '{{lastname}}', '{{postal}}', '{{city}}'];
        $orderData = $this->getOrderData($orderInstance);
        $message = $this->getNewOrderMsgCustomer();
        return str_replace($dynamicVar, $orderData, $message);
    }

    /**
     * @return mixed
     */
    public function getNewOrderMsgCustomer()
    {
        return $this->scopeConfig->getValue('messentesms/order/message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $orderInstance
     * @return array
     */
    public function getOrderData($orderInstance)
    {
        $addressDetail = $orderInstance->getBillingAddress();
        $orderDataList = array($orderInstance->getIncrementId(),
            $addressDetail->getEmail(),
            $addressDetail->getFirstname() . ' ' . $addressDetail->getLastname(),
            $addressDetail->getFirstname(),
            $addressDetail->getMiddlename(),
            $addressDetail->getLastname(),
            $addressDetail->getPostcode(),
            $addressDetail->getCity()
        );
        return $orderDataList;
    }

    /**
     * @return mixed
     */
    public function getCustomerOrderStatus()
    {
        return $this->scopeConfig->getValue('messentesms/order_status/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $orderInstance
     * @return mixed
     */
    public function orderStatusMessageCustomer($orderInstance)
    {
        $dynamicVar = ['{{order_id}}', '{{order_status}}', '{{email}}', '{{name}}', '{{firstname}}', '{{middlename}}', '{{lastname}}', '{{postal}}'];
        $message = $this->getOrderStatusMessageCustomer($orderInstance);
        $orderStatusData = $this->getOrderData($orderInstance);
        return str_replace($dynamicVar, $orderStatusData, $message);
    }

    /**
     * @param $orderInstance
     * @return mixed
     */
    public function getOrderStatusMessageCustomer($orderInstance)
    {
        $orderState = $orderInstance->getState();
        $configPathVal = 'messentesms/order_status/' . $orderState . '_message_customer';
        return $this->scopeConfig->getValue($configPathVal, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $orderInstance
     * @return mixed
     */
    public function getCustomerOrderNumber($orderInstance)
    {
        $addressDetail = $orderInstance->getBillingAddress();
        $countryId = $addressDetail->getCountryId();
        $number = $addressDetail->getTelephone();
        $dialCode = $this->getCountryCode($countryId);
        if (empty($dialCode)){
            $this->customLogger->error('Country code is not available for sms delivery');
        }
        return $dialCode.$number;
    }

    /**
     * @return mixed
     */
    public function getAdminOrderStatus()
    {
        return $this->scopeConfig->getValue('messentesms/order_status/admin_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $orderInstance
     * @return mixed
     */
    public function getAdminOrderStatusMessage($orderInstance)
    {
        $dynamicVar = ['{{order_id}}', '{{order_status}}', '{{email}}', '{{name}}', '{{firstname}}', '{{middlename}}', '{{lastname}}', '{{postal}}'];
        $orderDetail = $this->getOrderData($orderInstance);
        $message = $this->getOrderStatusMsgAdmin($orderInstance);
        return str_replace($dynamicVar, $orderDetail, $message);
    }

    /**
     * @param $orderInstance
     * @return mixed
     */
    public function getOrderStatusMsgAdmin($orderInstance)
    {
        $state = $orderInstance->getState();
        $configPath = 'messentesms/order_status/' . $state . '_message_admin';
        return $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getAdminOrderNumber()
    {
        return $this->scopeConfig->getValue('messentesms/order_status/admin_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $orderInstance
     * @return mixed
     */
    public function getOrderCustomerNumber($orderInstance)
    {
        $addressDetail = $orderInstance->getBillingAddress();
        $countryId = $addressDetail->getCountryId();
        $number = $addressDetail->getTelephone();
        $dialCode = $this->getCountryCode($countryId);
        if (empty($dialCode)){
            $this->customLogger->error('Country code is not available for sms delivery');
        }
        return $dialCode.$number;
    }

    /**
     * @return mixed
     */
    public function getNewOrderNotificationAdmin()
    {
        return $this->scopeConfig->getValue('messentesms/order/admin_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $orderInstance
     * @return mixed
     */
    public function getNewOrderMessageAdmin($orderInstance)
    {
        $dynamicVar = ['{{order_id}}', '{{email}}', '{{name}}', '{{firstname}}', '{{middlename}}', '{{lastname}}', '{{postal}}', '{{city}}'];
        $orderDataList = $this->getOrderData($orderInstance);
        $message = $this->getAdminNewOrderMsg();
        return str_replace($dynamicVar, $orderDataList, $message);
    }

    /**
     * @return mixed
     */
    public function getOrderMessageDeliveryNumberAdmin()
    {
        return $this->scopeConfig->getValue('messentesms/order/admin_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getAdminNewOrderMsg()
    {
        return $this->scopeConfig->getValue('messentesms/order/admin_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getSiteWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @return array
     */
    public function getCredentials()
    {
        $response = [];
        $response['username'] = $this->scopeConfig->getValue('messentesms/general/api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $response['password'] = $this->scopeConfig->getValue('messentesms/general/sender_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $response;
    }

    /**
     * @param $id
     * @return $this
     */
    public function getOrder($id)
    {
        return $this->order->load($id);
    }

    /**
     * @param $smsto
     * @param $message
     */
    public function sendSms($smsto, $message)
    {
        $credentials = $this->getCredentials();
        $config = Configuration::getDefaultConfiguration()
            ->setUsername($credentials['username'])
            ->setPassword($credentials['password']);
        $client = new \GuzzleHttp\Client();
        $apiInstance = new OmnimessageApi($client, $config);
        $omnimessage = new Omnimessage([
            'to' => $smsto,
        ]);
        $sms = new SMS(
            [
                'text' => $message,
                'sender' => '',
            ]
        );
        $omnimessage->setMessages([$sms]);
        try {
            $result = $apiInstance->sendOmnimessage($omnimessage);
            $this->customLogger->info('Messente API response : ' . json_encode($result));
        } catch (\Exception $e) {
            $this->customLogger->info('Messente response for mobile number ' . $smsto);
        }
    }

    /**
     * @return string
     */
    public function getCustomerRegistrationTemplate()
    {
        if ($this->scopeConfig->isSetFlag('messentesms/general/enable')) {
            $template = 'Abhinay_Messente::customer/registration.phtml';
        } else {
            $template = 'Magento_Customer::form/register.phtml';
        }
        return $template;
    }

    public function getCodeList()
    {
        return $this->scopeConfig->getValue('messentesms/general/country_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCountryCode($currentCode)
    {
        $dialCode = '';
        $codes = $this->getCodeList();
        $dialCodeList = json_decode($codes, true);
        if (!empty($dialCodeList)) {
            foreach ($dialCodeList as $key => $data) {
                if ($currentCode == $data['country_id']) {
                    $dialCode = $data['code'];
                }
            }
        }
        return $dialCode;
    }
}
