<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Mail\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder as MageTransportBuilder;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;

/**
 * TransportBuilder is able to prepare and send emails
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class TransportBuilder extends MageTransportBuilder
{
    /**
     * Param that used for storing all message data until it will be used
     *
     * @var array
     */
    private $messageData = [];
    /**
     * @var EmailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;
    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;
    /**
     * @var MimePartInterfaceFactory
     */
    private $mimePartInterfaceFactory;
    /**
     * @var AddressConverter|null
     */
    private $addressConverter;
    /**
     * @var array
     */
    private $attachments = [];

    /**
     * TransportBuilder constructor
     *
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param EmailMessageInterfaceFactory $emailMessageInterfaceFactory
     * @param MimeMessageInterfaceFactory $mimeMessageInterfaceFactory
     * @param MimePartInterfaceFactory $mimePartInterfaceFactory
     * @param addressConverter $addressConverter
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory,
        MimePartInterfaceFactory $mimePartInterfaceFactory,
        AddressConverter $addressConverter
    ) {
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory;
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory;
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->addressConverter = $addressConverter;

        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            null,
            $emailMessageInterfaceFactory,
            $mimeMessageInterfaceFactory,
            $mimePartInterfaceFactory,
            $addressConverter
        );
    }

    /**
     * Add cc address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return TransportBuilder
     */
    public function addCc($address, $name = '')
    {
        $this->addAddressByType('cc', $address, $name);

        return $this;
    }

    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return TransportBuilder
     * @throws InvalidArgumentException
     */
    public function addTo($address, $name = '')
    {
        $this->addAddressByType('to', $address, $name);

        return $this;
    }

    /**
     * Add bcc address
     *
     * @param array|string $address
     *
     * @return TransportBuilder
     * @throws InvalidArgumentException
     */
    public function addBcc($address)
    {
        $this->addAddressByType('bcc', $address);

        return $this;
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string|null $name
     *
     * @return TransportBuilder
     * @throws InvalidArgumentException
     */
    public function setReplyTo($email, $name = null)
    {
        $this->addAddressByType('replyTo', $email, $name);

        return $this;
    }

    /**
     * Set mail from address by scopeId
     *
     * @param string|array $from
     * @param string|int $scopeId
     *
     * @return TransportBuilder
     * @throws InvalidArgumentException
     * @throws MailException
     * @since 102.0.1
     */
    public function setFromByScope($from, $scopeId = null)
    {
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $result['email'], $result['name']);

        return $this;
    }

    /**
     * Set template identifier
     *
     * @param string $templateIdentifier
     *
     * @return TransportBuilder
     */
    public function setTemplateIdentifier($templateIdentifier)
    {
        $this->templateIdentifier = $templateIdentifier;

        return $this;
    }

    /**
     * Set template model
     *
     * @param string $templateModel
     *
     * @return TransportBuilder
     */
    public function setTemplateModel($templateModel)
    {
        $this->templateModel = $templateModel;
        return $this;
    }

    /**
     * Set template vars
     *
     * @param array $templateVars
     *
     * @return TransportBuilder
     */
    public function setTemplateVars($templateVars)
    {
        $this->templateVars = $templateVars;

        return $this;
    }

    /**
     * Set template options
     *
     * @param array $templateOptions
     * @return TransportBuilder
     */
    public function setTemplateOptions($templateOptions)
    {
        $this->templateOptions = $templateOptions;

        return $this;
    }

    /**
     * Get mail transport
     *
     * @return TransportInterface
     * @throws LocalizedException
     */
    public function getTransport()
    {
        try {
            $this->prepareMessage();
            $mailTransport = $this->mailTransportFactory->create(['message' => clone $this->message]);
        } finally {
            $this->reset();
        }

        return $mailTransport;
    }

    /**
     * Reset object state
     *
     * @return TransportBuilder
     */
    protected function reset()
    {
        $this->messageData = [];
        $this->templateIdentifier = null;
        $this->templateVars = null;
        $this->templateOptions = null;
        return $this;
    }

    /**
     * Get template
     *
     * @return TemplateInterface
     */
    protected function getTemplate()
    {
        return $this->templateFactory->get($this->templateIdentifier, $this->templateModel)
            ->setVars($this->templateVars)
            ->setOptions($this->templateOptions);
    }

    // phpcs:disable Magento2.Functions.DiscouragedFunction
    /**
     * Prepare message.
     *
     * @return TransportBuilder
     * @throws LocalizedException if template type is unknown
     */
    protected function prepareMessage(): self
    {
        $template = $this->getTemplate();
        $content = $template->processTemplate();

        switch ($template->getType()) {
            case TemplateTypesInterface::TYPE_TEXT:
                $partType = MimeInterface::TYPE_TEXT;
                break;

            case TemplateTypesInterface::TYPE_HTML:
                $partType = MimeInterface::TYPE_HTML;
                break;

            default:
                throw new LocalizedException(
                    new Phrase('Unknown template type')
                );
        }

        /** @var MimePartInterface $mimePart */
        $mimePart = $this->mimePartInterfaceFactory->create(
            [
                'content' => $content,
                'type' => $partType
            ]
        );
        $parts = count($this->attachments) ? array_merge([$mimePart], $this->attachments) : [$mimePart];
        $this->messageData['encoding'] = $mimePart->getCharset();
        $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(
            ['parts' => $parts]
        );

        $this->messageData['subject'] = html_entity_decode(
            (string)$template->getSubject(),
            ENT_QUOTES
        );

        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);

        return $this;
    }

    /**
     * Handles possible incoming types of email (string or array)
     *
     * @param string $addressType
     * @param string|array $email
     * @param string|null $name
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function addAddressByType(string $addressType, $email, ?string $name = null): void
    {
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
            return;
        }
        $this->messageData[$addressType] = $convertedAddressArray;
    }

    /**
     * Adds attachment to email
     *
     * @param string $content
     * @param string $fileName
     * @return TransportBuilder
     */
    public function addAttachment(string $content, string $fileName): self
    {
        $mimePartAttachment = $this->mimePartInterfaceFactory->create([
            'content' => $content,
            'fileName' => $fileName,
            'disposition' => MimeInterface::DISPOSITION_ATTACHMENT,
            'encoding' => MimeInterface::ENCODING_BASE64,
            'type' => MimeInterface::TYPE_OCTET_STREAM
        ]);

        $this->attachments[] = $mimePartAttachment;

        return $this;
    }
}
