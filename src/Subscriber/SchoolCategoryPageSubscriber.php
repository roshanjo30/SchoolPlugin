<?php declare(strict_types=1);

namespace SchoolPlugin\Subscriber;

use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SchoolCategoryPageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $schoolRepository
    ) {
    }


    public static function getSubscribedEvents(): array
    {
        return [
            NavigationPageLoadedEvent::class => 'onNavigationPageLoaded',
        ];
    }


    public function onNavigationPageLoaded(
        NavigationPageLoadedEvent $event
    ): void {

        $category = $event->getPage()->getCategory();

        if ($category === null) {
            return;
        }


        $criteria = new Criteria();

        $criteria->addFilter(
            new EqualsFilter(
                'categoryId',
                $category->getId()
            )
        );


        $school = $this->schoolRepository
            ->search(
                $criteria,
                $event->getContext()
            )
            ->first();


        if ($school === null) {
            return;
        }
        


        $category->addExtension(
            'school',
            new ArrayEntity([
                'id' => $school->getId(),
                'schoolName' => $school->getSchoolName(),
                'email' => $school->getEmail(),
                'contactPerson' => $school->getContactPerson(),
                'categoryId' => $school->getCategoryId(),
            ])
        );
    }
}