<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2017 Emarsys. (http://www.emarsys.net/)
 */

namespace Emarsys\Emarsys\Model;

use Magento\Framework\Mail\MailMessageInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Email\Model\TransportFactory as MagentoTransportFactory;

/**
 * Class Transport
 * @package Emarsys\Emarsys\Model
 */
class Transport implements TransportInterface
{
    /**
     * @var MailMessageInterface
     */
    protected $_message;

    /**
     * @var SendEmail
     */
    protected $sendEmail;

    /**
     * @var MagentoTransportFactory
     */
    protected $magentoTransportFactory;

    /**
     * Transport constructor.
     * @param MailMessageInterface $message
     * @param SendEmail $sendEmail
     * @param null $parameters
     */
    public function __construct(
        MailMessageInterface $message,
        SendEmail $sendEmail,
        MagentoTransportFactory $magentoTransportFactory,
        $parameters = null
    ) {
        if (!$message instanceof MailMessageInterface) {
            throw new \InvalidArgumentException('The message should be an instance of Magento\Framework\Mail\MailMessageInterface');
        }

        $this->_message = $message;
        $this->sendEmail = $sendEmail;
        $this->magentoTransportFactory = $magentoTransportFactory;
    }

    public function sendMessage()
    {
        $mailSendingStatus = $this->sendEmail->sendMail($this->_message);

        //if we failed to relay through Emarsys for any reason, fallback to local relay via Sendmail
        if ($mailSendingStatus) {
            //instantiate the stock Magento Framework Sendmail system to relay this
            /** @see \Magento\Framework\Mail\Template\TransportBuilder::getTransport */
            $magentoTransport = $this->magentoTransportFactory->create(
                ['message' => clone $this->_message]
            );
            $magentoTransport->sendMessage();
        }
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->_message;
    }
}
