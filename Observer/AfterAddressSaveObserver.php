<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2017 Emarsys. (http://www.emarsys.net/)
 */

namespace Emarsys\Emarsys\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Emarsys\Emarsys\Helper\Data;
use Emarsys\Emarsys\Model\Api\Contact;
use Emarsys\Emarsys\Model\Logs;
use Emarsys\Emarsys\Model\ResourceModel\Customer;

/**
 * Class AfterAddressSaveObserver
 * @package Emarsys\Emarsys\Observer
 */
class AfterAddressSaveObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    private $emarsysHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Contact
     */
    private $contactModel;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Customer
     */
    private $customerResourceModel;

    /**
     * @var Logs
     */
    private $emarsysLogs;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * AfterAddressSaveObserver constructor.
     *
     * @param Data $emarsysHelper
     * @param Registry $registry
     * @param Contact $contactModel
     * @param StoreManagerInterface $storeManager
     * @param Customer $customerResourceModel
     * @param Logs $emarsysLogs
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Data $emarsysHelper,
        Registry $registry,
        Contact $contactModel,
        StoreManagerInterface $storeManager,
        Customer $customerResourceModel,
        Logs $emarsysLogs,
        CustomerFactory $customerFactory
    ) {
        $this->emarsysHelper = $emarsysHelper;
        $this->registry = $registry;
        $this->contactModel = $contactModel;
        $this->storeManager = $storeManager;
        $this->customerResourceModel = $customerResourceModel;
        $this->emarsysLogs = $emarsysLogs;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var $customerAddress Address */
            $customerAddress = $observer->getCustomerAddress();
            $customer = $customerAddress->getCustomer();
            $customerObj = $this->customerFactory->create()->load($customer->getId());

            $customerId = $customerObj->getEntityId();
            $websiteId = $customerObj->getWebsiteId();
            $defaultBillingId = $customerObj->getDefaultBilling();
            $defaultShippingId = $customerObj->getDefaultShipping();

            if (!empty($defaultBillingId) || !empty($defaultShippingId)) {
                if (!in_array($customerAddress->getId(), [$defaultBillingId, $defaultShippingId])) {
                    return;
                }
            }

            if (!$this->emarsysHelper->isContactsSynchronizationEnable($websiteId)) {
                return;
            }

            $storeId = $customerObj->getStoreId();

            $customerVar = 'create_customer_variable_' . $customerId;
            if ($this->registry->registry($customerVar) == 'created') {
                return;
            }
            $this->contactModel->syncContact($customer, $websiteId, $storeId, 0, $customerAddress);
            $this->registry->register($customerVar, 'created');
        } catch (\Exception $e) {
            $this->emarsysLogs->addErrorLog(
                $e->getMessage(),
                $this->storeManager->getStore()->getId(),
                'AfterAddressSaveObserver::execute()'
            );
        }
    }
}
