<?php declare(strict_types=1);

namespace SchoolPlugin\Event;

use Shopware\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Symfony\Contracts\EventDispatcher\Event;

final class SchoolParentInvitedEvent extends Event implements
    SalesChannelAware,
    MailAware,
    ScalarValuesAware,
    FlowEventAware
{
    public const EVENT_NAME = 'school.parent.invitation';

    public function __construct(
        private readonly Context $context,
        private readonly string $salesChannelId,
        private readonly MailRecipientStruct $recipients,
        private readonly string $schoolName,
        private readonly string $categoryUrl,
        private readonly string $parentName
    ) {
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add(
                'schoolName',
                new ScalarValueType(ScalarValueType::TYPE_STRING)
            )
            ->add(
                'parentName',
                new ScalarValueType(ScalarValueType::TYPE_STRING)
            )
            ->add(
                'categoryUrl',
                new ScalarValueType(ScalarValueType::TYPE_STRING)
            );
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }
    
    /**
     * @return array<string, mixed>
     */
    public function getValues(): array
    {
        return [
            'schoolName' => $this->schoolName,
            'parentName' => $this->parentName,
            'categoryUrl' => $this->categoryUrl,
        ];
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getMailStruct(): MailRecipientStruct
    {
        return $this->recipients;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }
}