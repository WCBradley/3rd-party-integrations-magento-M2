<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2018 Emarsys. (http://www.emarsys.net/)
 */
namespace Emarsys\Emarsys\Helper;

use Magento\{
    Framework\App\Helper\AbstractHelper,
    Framework\App\Helper\Context,
    Store\Model\StoreManagerInterface,
    AdminNotification\Model\InboxFactory
};
use Emarsys\Emarsys\{
    Model\ResourceModel\Event as EmarsysResourceModelEvent,
    Model\EmarsyseventsFactory
};

/**
 * Class Event
 * @package Emarsys\Emarsys\Helper
 */
class Event extends AbstractHelper
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $emarsysHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Event constructor.
     * @param Data $emarsysHelper
     * @param EmarsysResourceModelEvent $resourceModelEvent
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param InboxFactory $adminNotification
     * @param EmarsyseventsFactory $emarsysEvents
     */
    public function __construct(
        Data $emarsysHelper,
        EmarsysResourceModelEvent $resourceModelEvent,
        Context $context,
        StoreManagerInterface $storeManager,
        InboxFactory $adminNotification,
        EmarsyseventsFactory $emarsysEvents
    ) {
        ini_set('default_socket_timeout', 1000);
        $this->logger = $context->getLogger();
        $this->storeManager = $storeManager;
        $this->emarsysHelper = $emarsysHelper;
        $this->resourceModelEvent = $resourceModelEvent;
        $this->adminNotification = $adminNotification;
        $this->emarsysEvents = $emarsysEvents;
    }

    /**
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEventSchema()
    {
        try {
            $response = $this->emarsysHelper->send('GET', 'event');
            return \Zend_Json::decode($response);
        } catch (\Exception $e) {
            $storeId = $this->storeManager->getStore()->getId();
            $this->emarsysHelper->addErrorLog($e->getMessage(), $storeId, 'getEventSchema');
            return false;
        }
    }

    /**
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEventTemplateSchema()
    {
        try {
            $response = $this->emarsysHelper->send('GET', 'email/templates');
            return \Zend_Json::decode($response);
        } catch (\Exception $e) {
            $storeId = $this->storeManager->getStore()->getId();
            $this->emarsysHelper->addErrorLog($e->getMessage(), $storeId, 'getEventTemplateSchema');
            return false;
        }
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveEmarsysEventSchemaNotification()
    {
        try {
            $adminNotiColl = $this->adminNotification->create();
            $adminNotiColl->setSeverity(4);
            $adminNotiColl->setTitle('Emarsys Events Updates');
            $adminNotiColl->setDescription('Emarsys events has been update, Please update the emarsys event schema');
            $adminNotiColl->save();
        } catch (\Exception $e) {
            $storeId = $this->storeManager->getStore()->getId();
            $this->emarsysHelper->addErrorLog($e->getMessage(), $storeId, 'saveEmarsysEventSchemaNotification');
            return false;
        }
        return true;
    }

    /**
     * @param $websiteId
     * @return array|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLocalEmarsysEvents($websiteId)
    {
        $emarsysLocalIds = [];
        try {
            $defaultStore = $this->storeManager->getWebsite($websiteId)->getDefaultStore();
            if ($defaultStore) {
                $defaultStore = $defaultStore->getId();
            } else {
                throw new \Exception(__('There is no default store selected for website id %1', $websiteId));
            }
            $emarsysContactFields = $this->resourceModelEvent->getEmarsysEvents($defaultStore);

            foreach ($emarsysContactFields as $_emarsysContactField) {
                $emarsysLocalIds[] = $_emarsysContactField['event_id'];
            }
        } catch (\Exception $e) {
            $storeId = $this->storeManager->getStore()->getId();
            $this->emarsysHelper->addErrorLog($e->getMessage(), $storeId, 'getLocalEmarsysEvents');
        }
        return $emarsysLocalIds;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEmar()
    {
        try {
            $adminNotiColl = $this->emarsysEvents->create()->getCollection();
            print_r($adminNotiColl->getData());
            foreach ($adminNotiColl as $_adminNotiColl) {
                print_r($_adminNotiColl->getData());
            }
        } catch (\Exception $e) {
            $storeId = $this->storeManager->getStore()->getId();
            $this->emarsysHelper->addErrorLog($e->getMessage(), $storeId, 'getEmar');
            return false;
        }
    }
}
