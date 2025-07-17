<?php

namespace Eadesigndev\Pdfgenerator\Model\Email;

use Magento\Framework\Mail\MailMessageInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Part\DataPart;

/**
 * Class Message
 * @package Eadesigndev\Pdfgenerator\Model\Email
 */
class Message extends \Magento\Framework\Mail\Message implements MailMessageInterface
{

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageFactory;

    /**
     * @var Email
     */
    private $message;

    /**
     * @var MimePartInterface
     */
    private $attachment;

    /**
     * @var string
     */
    private $messageType = self::TYPE_TEXT;

    public function __construct(MimeMessageInterfaceFactory $mimeMessageFactory, $charset = 'utf-8')
    {
        parent::__construct($charset);
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->message = new Email();
        $this->message->getHeaders()->addTextHeader('Content-Type', 'text/plain; charset=' . $charset);
    }

    public function setBodyAttachment(string $content, string $fileName, string $contentType = 'application/pdf'): self
    {
        $attachmentPart = new DataPart($content, $fileName, $contentType);
        $this->attachment = $this->mimeMessageFactory->createMimePart($attachmentPart);
        $this->message->attach($attachmentPart);
        return $this;
    }

    public function setMessageType($type):self
    {
        $this->messageType = $type;
        return $this;
    }

    public function setBody($body): self
    {
        if (is_string($body) && $this->messageType === self::TYPE_HTML) {
            $body = new TextPart($body, 'utf-8', 'html');
        } elseif (is_string($body)) {
            $body = new TextPart($body, 'utf-8', 'plain');
        }
        if ($this->attachment) {
            $this->message->setBody($body);
            $this->message->attach($this->attachment->getBody());
        } else {
            $this->message->setBody($body);
        }
        return $this;
    }

    public function setSubject($subject): self
    {
        $this->message->subject($subject);
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->message->getSubject();
    }

    public function getBody(): \Symfony\Component\Mime\Part\AbstractPart
    {
        return $this->message->getBody();
    }

    public function setFromAddress($fromAddress, $fromName = null): self
    {
        $this->message->from(new \Symfony\Component\Mime\Address($fromAddress, $fromName ?? ''));
        return $this;
    }

    public function addTo($toAddress, ?string $toName = null): self
    {
        $this->message->addTo(new \Symfony\Component\Mime\Address($toAddress, $toName ?? ''));
        return $this;
    }

    public function addCc($ccAddress, ?string $ccName = null): self
    {
        $this->message->addCc(new \Symfony\Component\Mime\Address($ccAddress, $ccName ?? ''));
        return $this;
    }

    public function addBcc($bccAddress, ?string $bccName = null): self
    {
        $this->message->addBcc(new \Symfony\Component\Mime\Address($bccAddress, $bccName ?? ''));
        return $this;
    }

    public function setReplyTo($replyToAddress, ?string $replyToName = null): self
    {
        $this->message->replyTo(new \Symfony\Component\Mime\Address($replyToAddress, $replyToName ?? ''));
        return $this;
    }

    public function getRawMessage(): string
    {
        return $this->message->toString();
    }

    public function setBodyHtml($html): self
    {
        $this->setMessageType(self::TYPE_HTML);
        return $this->setBody($html);
    }

    public function setBodyText($text): self
    {
        $this->setMessageType(self::TYPE_TEXT);
        return $this->setBody($text);
    }
}
