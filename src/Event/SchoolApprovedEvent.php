<?php declare(strict_types=1);

namespace SchoolPlugin\Event;

use SchoolPlugin\Core\Content\School\SchoolEntity;
use Shopware\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ObjectType;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Symfony\Contracts\EventDispatcher\Event;

final class SchoolApprovedEvent extends Event implements
    SalesChannelAware,
    MailAware,
    ScalarValuesAware,
    FlowEventAware
{
    public const EVENT_NAME = 'school.approved';

    /**
     * @var array<string, scalar|array<mixed>|null>
     */
    private readonly array $schoolData;

    public function __construct(
        private readonly Context $context,
        private readonly string $salesChannelId,
        private readonly MailRecipientStruct $recipients,
        SchoolEntity $school,
        string $categoryUrl
    ) {
        $this->schoolData = [
            'schoolName' => $school->getSchoolName(),
            'contactPerson' => $school->getContactPerson(),
            'email' => $school->getEmail(),
            'phone' => $school->getPhone(),
            'street' => $school->getStreet(),
            'zipcode' => $school->getZipcode(),
            'city' => $school->getCity(),
            'categoryUrl' => $categoryUrl,
        ];
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('school', new ObjectType());
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    /**
     * @return array<string, scalar|array<mixed>|null>
     */
    public function getValues(): array
    {
        return [
            'school' => $this->schoolData,
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