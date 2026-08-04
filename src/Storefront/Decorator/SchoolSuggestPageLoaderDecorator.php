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

        foreach ($page->getSearchResult()->getEntities() as $product) {
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
                    $salesChannelContext->getContext()
                )
                ->first();

            if (!$schoolPrice) {
                continue;
            }

            $product->addExtension(
                'schoolPrice',
                new ArrayEntity([
                    'price' => (float)$schoolPrice->getPrice()
                ])
            );
        }
        return $page;
    }
}