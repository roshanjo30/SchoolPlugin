<?php declare(strict_types=1);

namespace SchoolPlugin\Subscriber;

use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SchoolCategoryPageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $schoolRepository,
        private readonly RequestStack $requestStack,
        private readonly CartService $cartService
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
    
        $session = $this->requestStack->getSession();
        if (!$session) {
            return;
        }
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
            if ($session->get('current_store') === 'school') {
                $cart = $this->cartService->getCart(
                    $event->getSalesChannelContext()->getToken(),
                    $event->getSalesChannelContext()
                );
                if ($cart->getLineItems()->count() > 0) {
                    $this->cartService->removeItems(
                        $cart,
                        $cart->getLineItems()->getKeys(),
                        $event->getSalesChannelContext()
                    );
                }
                $session->remove('selected_school_id');
                $session->remove('school_invitation_token');
                $session->set('current_store', 'storefront');
            }
            return;
        }
    
        $session->set('current_store', 'school');
        $session->set('selected_school_id', $school->getId());
    
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