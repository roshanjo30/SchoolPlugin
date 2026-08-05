<?php declare(strict_types=1);

namespace SchoolPlugin\Storefront\Decorator;

use Shopware\Storefront\Page\Suggest\SuggestPageLoader;
use Shopware\Storefront\Page\Suggest\SuggestPage;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;

class SchoolSuggestPageLoaderDecorator extends SuggestPageLoader
{
    public function __construct(
        private readonly SuggestPageLoader $decorated,
        private readonly EntityRepository $schoolProductPriceRepository
    ) {
    }

    public function load(
        Request $request,
        SalesChannelContext $salesChannelContext
    ): SuggestPage {

        $page = $this->decorated->load(
            $request,
            $salesChannelContext
        );

        $session = $request->getSession();
        if (!$session) {
            return $page;
        }

        $schoolId = $session->get('selected_school_id');

        if (!$schoolId) {
            return $page;
        }

        $productIds = [];

        foreach ($page->getSearchResult()->getEntities() as $product) {
            $productIds[] = $product->getId();
        }

        $productIds = array_values(array_unique($productIds));

        if (empty($productIds)) {
            return $page;
        }

        $criteria = new Criteria();

        $criteria->addFilter(
            new EqualsFilter(
                'schoolId',
                $schoolId
            )
        );

        $criteria->addFilter(
            new EqualsAnyFilter(
                'productId',
                $productIds
            )
        );

        $prices = $this->schoolProductPriceRepository->search(
            $criteria,
            $salesChannelContext->getContext()
        );

        $priceMap = [];

        /** @var \SchoolPlugin\Core\Content\SchoolProductPrice\SchoolProductPriceEntity $schoolPrice */
        foreach ($prices as $schoolPrice) {
            $priceMap[$schoolPrice->getProductId()] = (float) $schoolPrice->getPrice();
        }

        foreach ($page->getSearchResult()->getEntities() as $product) {
            if (!isset($priceMap[$product->getId()])) {
                continue;
            }

            $product->addExtension(
                'schoolPrice',
                new ArrayEntity([
                    'price' => $priceMap[$product->getId()]
                ])
            );
        }
        return $page;
    }
}