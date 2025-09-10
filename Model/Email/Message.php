<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Eadesigndev\Pdfgenerator\Model\Email;

use Magento\Framework\Mail\MailMessageInterface;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Message as SymfonyMessage;

/**
 * Class Message for email transportation with PDF attachment support
 *
 * @package Eadesigndev\Pdfgenerator\Model\Email
 */
class Message extends \Magento\Framework\Mail\Message implements MailMessageInterface
{
    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageFactory;

    /**
     * @var MimePartInterface
     */
    private $attachment;

    /**
     * @var string
     */
    private $messageType = MimeInterface::TYPE_TEXT;

    /**
     * Initialize dependencies.
     *
     * @param MimeMessageInterfaceFactory $mimeMessageFactory
     * @param string $charset
     */
    public function __construct(
        MimeMessageInterfaceFactory $mimeMessageFactory,
        string $charset = 'utf-8'
    ) {
        parent::__construct($charset);
        $this->mimeMessageFactory = $mimeMessageFactory;
    }

    /**
     * Set attachment for the email.
     *
     * @param string $content
     * @param string $fileName
     * @param string $contentType
     * @return $this
     */
    public function setBodyAttachment(string $content, string $fileName, string $contentType = 'application/pdf'): self
    {
        $attachmentPart = new DataPart($content, $fileName, $contentType, 'base64');
        $attachmentPart->setDisposition('attachment');
        $this->attachment = $this->mimeMessageFactory->create(['data' => $attachmentPart]);
        return $this;
    }

    /**
     * Set the message type (text or HTML).
     *
     * @param string $type
     * @return $this
     * @deprecated Use setBodyHtml or setBodyText directly
     */
    public function setMessageType($type): self
    {
        $this->messageType = $type;
        return $this;
    }

    /**
     * Set the email body.
     *
     * @param string|\Symfony\Component\Mime\Part\TextPart|\Symfony\Component\Mime\Message $body
     * @return $this
     */
    public function setBody($body): self
    {
        if (is_string($body)) {
            $body = $this->createMimeFromString($body, $this->messageType);
        }
        if ($this->attachment) {
            $currentBody = $body->getBody();
            if ($currentBody instanceof TextPart) {
                $parts = [$currentBody, $this->attachment->getBody()];
                $body = $this->mimeMessageFactory->create(['parts' => $parts]);
            } else {
                $body->attach($this->attachment->getBody());
            }
        }
        $this->symfonyMessage = $body;
        return $this;
    }

    /**
     * Create mime message from the string.
     *
     * @param string $body
     * @param string $messageType
     * @return SymfonyMessage
     */
    private function createMimeFromString(string $body, string $messageType): SymfonyMessage
    {
        if ($messageType == MimeInterface::TYPE_HTML) {
            $part = new TextPart($body, $this->charset, 'html', MimeInterface::ENCODING_QUOTED_PRINTABLE);
            $part->setDisposition('inline');
            return new SymfonyMessage(null, $part);
        }

        $part = new TextPart($body, $this->charset, 'plain', MimeInterface::ENCODING_QUOTED_PRINTABLE);
        $part->setDisposition('inline');
        return new SymfonyMessage(null, $part);
    }

    /**
     * Set the email body as HTML.
     *
     * @param mixed $html
     * @return $this
     */
    public function setBodyHtml($html): self
    {
        $this->setMessageType(MimeInterface::TYPE_HTML);
        return $this->setBody($html);
    }

    /**
     * Set the email body as plain text.
     *
     * @param mixed $text
     * @return $this
     */
    public function setBodyText($text): self
    {
        $this->setMessageType(MimeInterface::TYPE_TEXT);
        return $this->setBody($text);
    }
}
