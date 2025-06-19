<?php

namespace Eadesigndev\Pdfgenerator\Model\Email;

use Magento\Framework\Mail\MailMessageInterface;
use Laminas\Mime\Mime;
use Laminas\Mime\PartFactory;
use Laminas\Mail\MessageFactory as MailFactory;
use Laminas\Mime\MessageFactory as MimeFactory;
use Laminas\Mime\Part;
use Symfony\Component\Mime\Part\AbstractPart;

/**
 * Class Message
 * @package Eadesigndev\Pdfgenerator\Model\Email
 * @deprecated
 */
class Message extends \Magento\Framework\Mail\Message implements MailMessageInterface
{

    /**
     * @var PartFactory
     */
    private $partFactory;

    /**
     * @var MimeFactory
     */
    private $mimeMessageFactory;

    protected $laminasMessage;

    private $attachment;

    private $messageType = self::TYPE_TEXT;

    public function __construct(
        PartFactory $partFactory,
        MimeFactory $mimeMessageFactory,
        $charset = 'utf-8'
    ) {
        $this->partFactory = $partFactory;
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->laminasMessage = MailFactory::getInstance();
        $this->laminasMessage->setEncoding($charset);
    }

    public function setBodyAttachment($content, $fileName)
    {
        $attachmentPart = $this->partFactory->create();

        $attachmentPart->setContent($content)
            ->setType(Mime::TYPE_OCTETSTREAM)
            ->setEncoding(Mime::ENCODING_BASE64)
            ->setFileName($fileName)
            ->setDisposition(Mime::DISPOSITION_ATTACHMENT);

        $this->attachment = $attachmentPart;
        return $this;
    }

    public function setMessageType($type):self
    {
        $this->messageType = $type;
        return $this;
    }

    public function setBody($body): self
    {
        if (is_string($body) && $this->messageType === MailMessageInterface::TYPE_HTML) {
            $body = self::createHtmlMimeFromString($body);
        }

        $attachment = $this->attachment;
        if (isset($attachment)) {
            $body->addPart($attachment);
        }

        $this->laminasMessage->setBody($body);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject): self
    {
        $this->laminasMessage->setSubject($subject);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject(): ?string
    {
        return $this->laminasMessage->getSubject();
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): AbstractPart
    {
        return $this->laminasMessage->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function setFromAddress($fromAddress, $fromName = null): self
    {
        $this->laminasMessage->setFrom($fromAddress, $fromName);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTo($toAddress): self
    {
        $this->laminasMessage->addTo($toAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCc($ccAddress): self
    {
        $this->laminasMessage->addCc($ccAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addBcc($bccAddress): self
    {
        $this->laminasMessage->addBcc($bccAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setReplyTo($replyToAddress): self
    {
        $this->laminasMessage->setReplyTo($replyToAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawMessage(): string
    {
        return $this->laminasMessage->toString();
    }

    private function createHtmlMimeFromString($htmlBody)
    {
        $htmlPart = $this->partFactory->create(['content' => $htmlBody]);
        $htmlPart->setCharset($this->laminasMessage->getEncoding());
        $htmlPart->setType(Mime::TYPE_HTML);
        $mimeMessage = $this->mimeMessageFactory->create();
        $mimeMessage->addPart($htmlPart);
        return $mimeMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function setBodyHtml($html): self
    {
        $this->setMessageType(self::TYPE_HTML);
        return $this->setBody($html);
    }

    /**
     * {@inheritdoc}
     */
    public function setBodyText($text): self
    {
        $this->setMessageType(self::TYPE_TEXT);
        return $this->setBody($text);
    }
}
