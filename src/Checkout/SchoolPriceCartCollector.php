<?php declare(strict_types=1);

namespace SchoolPlugin\Checkout;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RequestStack;

class SchoolPriceCartCollector implements CartDataCollectorInterface
{
    public const KEY = 'school-plugin-school-prices';

    public function __construct(
        private readonly EntityRepository $schoolProductPriceRepository,
        private readonly RequestStack $requestStack
    ) {
    }

    public function collect(
        CartDataCollection $data,
        Cart $original,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        

        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return;
        }

        $session = $request->getSession();
        if ($session === null) {
            return;
        }

        $schoolId = $session->get('selected_school_id');
        if (!$schoolId) {
            return;
        }

        $productIds = [];

        foreach (
            $original->getLineItems()->filterFlatByType(LineItem::PRODUCT_LINE_ITEM_TYPE)
            as $lineItem
        ) {
            $referencedId = $lineItem->getReferencedId();

            if ($referencedId !== null) {
                $productIds[] = $referencedId;
            }
        }

        $productIds = array_values(array_unique($productIds));

        if ($productIds === []) {
            $data->set(self::KEY, []);

            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('schoolId', $schoolId)
        );
        $criteria->addFilter(
            new EqualsAnyFilter('productId', $productIds)
        );
        $criteria->addFilter(
            new EqualsFilter('active', true)
        );

        $priceMap = [];

        $prices = $this->schoolProductPriceRepository->search(
            $criteria,
            $context->getContext()
        );

        foreach ($prices as $price) {
            $priceMap[$price->getProductId()] = (float) $price->getPrice();
        }

        $data->set(self::KEY, $priceMap);
    }
}