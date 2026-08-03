<?php declare(strict_types=1);

namespace SchoolPlugin\Storefront\Subscriber;

use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SchoolProductDetailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $schoolProductPriceRepository,
        private readonly RequestStack $requestStack
    ) {
    }


    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded'
        ];
    }


    public function onProductPageLoaded(
        ProductPageLoadedEvent $event
    ): void {

        $session = $this->requestStack->getSession();

        if (!$session) {
            return;
        }


        $schoolId = $session->get('selected_school_id');

        if (!$schoolId) {
            return;
        }


        $product = $event->getPage()->getProduct();


        $criteria = new Criteria();

        $criteria->addFilter(
            new EqualsFilter(
                'schoolId',
                $schoolId
            )
        );

        $criteria->addFilter(
            new EqualsFilter(
                'productId',
                $product->getId()
            )
        );


        $schoolPrice = $this->schoolProductPriceRepository
            ->search(
                $criteria,
                $event->getSalesChannelContext()->getContext()
            )
            ->first();


        if (!$schoolPrice) {
            return;
        }


        $price = (float) $schoolPrice->getPrice();


        /*
         * Attach to PAGE, not product
         */
        $event->getPage()->addExtension(
            'schoolPrice',
            new ArrayEntity([
                'price' => $price
            ])
        );
    }
}