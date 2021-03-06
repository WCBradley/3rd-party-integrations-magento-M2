<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2018 Emarsys. (http://www.emarsys.net/)
 */

namespace Emarsys\Emarsys\Block\Adminhtml\Mapping\Order\Renderer;

use Magento\Framework\DataObject;

/**
 * Class EmarsysOrderField
 * @package Emarsys\Emarsys\Block\Adminhtml\Mapping\Order\Renderer
 */
class EmarsysOrderField extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var \Emarsys\Emarsys\Model\ResourceModel\Customer\Collection
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Emarsys\Emarsys\Model\ResourceModel\Sync
     */
    protected $syncResourceModel;

    /**
     * @var \Emarsys\Emarsys\Model\ResourceModel\Customer
     */
    protected $resourceModelCustomer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Emarsys\Emarsys\Helper\Data
     */
    protected $emarsysHelper;

    /**
     * EmarsysOrderField constructor.
     * @param \Magento\Backend\Model\Session $session
     * @param \Emarsys\Emarsys\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Emarsys\Emarsys\Model\ResourceModel\Customer $resourceModelCustomer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Emarsys\Emarsys\Helper\Data $emarsysHelper
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Emarsys\Emarsys\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $backendHelper,
        \Emarsys\Emarsys\Model\ResourceModel\Customer $resourceModelCustomer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Emarsys\Emarsys\Helper\Data $emarsysHelper
    ) {
        $this->session = $session;
        $this->collectionFactory = $collectionFactory;
        $this->backendHelper = $backendHelper;
        $this->resourceModelCustomer = $resourceModelCustomer;
        $this->_storeManager = $storeManager;
        $this->emarsysHelper = $emarsysHelper;
    }

    /**
     * @param DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(DataObject $row)
    {
        $session = $this->session->getData();
        $storeId = 0;
        if (isset($session['store'])) {
            $storeId = $session['store'];
        }
        $heading = $this->emarsysHelper->getSalesOrderCsvDefaultHeader($storeId, true);
        array_unshift($heading, 'website_id');
        if (in_array($row->getData('emarsys_order_field'), $heading) || (in_array($row->getData('magento_column_name'), $heading))) {
            $html = "<label >" . $row->getData('emarsys_order_field') . "  </label>";
        } else {
            $html = "<input class = 'admin__control-text emarsysatts' type='text' name = '" . $row->getData('magento_column_name') . "' value = '" . $row->getData('emarsys_order_field') . "'  />";
        }

        return $html;
    }
}
