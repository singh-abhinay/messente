<?php

namespace Abhinay\Messente\Observer;

/**
 * Class NewOrder
 * @package Abhinay\Messente\Observer
 */
class NewOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Abhinay\Messente\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * NewOrder constructor.
     * @param \Abhinay\Messente\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Abhinay\Messente\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (($this->helper->getMessenteSmsStatus()) && ($this->helper->getMessenteSmsStatus() != 0)) {
            $orderId = $observer->getEvent()->getOrderIds();
            $orderInstance = $this->helper->getOrder($orderId['0']);
            if (!empty($orderInstance)) {
                if ($this->helper->getNewOrderNotificationCustomer() != 0) {
                    $message = $this->helper->getNewOrderMessageCustomer($orderInstance);
                    $number = $this->helper->getOrderCustomerNumber($orderInstance);

                    $this->helper->sendSms($number, $message);
                }
                if ($this->helper->getNewOrderNotificationAdmin() != 0) {
                    $messageAdmin = $this->helper->getNewOrderMessageAdmin($orderInstance);
                    $numberAdmin = $this->helper->getOrderMessageDeliveryNumberAdmin();
                    $this->helper->sendSms($numberAdmin, $messageAdmin);
                }
            }
        }
    }
}
