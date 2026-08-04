<?php declare(strict_types=1);

namespace SchoolPlugin\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductDetailRoute;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SchoolPlugin\Core\Content\SchoolProductPrice\SchoolProductPriceEntity;
use Symfony\Component\Routing\Attribute\Route;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class SchoolPageController extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $schoolRepository,
        private readonly EntityRepository $productRepository,
    
        /**
         * @var EntityRepository<SchoolProductPriceEntity>
         */
        private readonly EntityRepository $schoolProductPriceRepository,
        private readonly ProductDetailRoute $productDetailRoute,
        private readonly RequestStack $requestStack,
        private readonly CartService $cartService
    ) {
    }

    #[Route(
        path: '/school/{schoolId}',
        name: 'frontend.school.page',
        methods: ['GET']
    )]
    public function index(
        string $schoolId,
        Request $request,
        SalesChannelContext $salesChannelContext
    ): Response {

        $context = $salesChannelContext->getContext();
        $session = $this->requestStack->getSession();
        $token = $session?->get('school_invitation_token');

        if (!$token) {
            throw $this->createNotFoundException();
        }

        $school = $this->schoolRepository
            ->search(
                new Criteria([$schoolId]),
                $context
            )
            ->first();

        if (!$school) {
            throw $this->createNotFoundException();
        }
        $session = $this->requestStack->getSession();

        if ($session) {
            $currentSchoolId = $session->get('selected_school_id');
                if ($currentSchoolId !== $schoolId) {
                    $cart = $this->cartService->getCart(
                        $salesChannelContext->getToken(),
                        $salesChannelContext
                    );
                    if ($cart->getLineItems()->count() > 0) {
                        $this->cartService->removeItems(
                            $cart,
                            $cart->getLineItems()->getKeys(),
                            $salesChannelContext
                        );
                    }
                }

                $session->set(
                    'current_store',
                    'school'
                );

                $session->set(
                    'selected_school_id',
                    $schoolId
                );
        }

        $priceCriteria = new Criteria();
        $priceCriteria->addFilter(
            new EqualsFilter(
                'schoolId',
                $schoolId
            )
        );

        $schoolPrices = $this->schoolProductPriceRepository
            ->search(
                $priceCriteria,
                $context
            )
            ->getEntities();

        $schoolPriceMap = [];

        /** @var SchoolProductPriceEntity $schoolPrice */
        foreach ($schoolPrices as $schoolPrice) {
            $schoolPriceMap[$schoolPrice->getProductId()] =
                $schoolPrice->getPrice();
        }

        $products = [];
        if ($school->getCategoryId()) {
            $criteria = new Criteria();
            $criteria->addFilter(
                new EqualsFilter(
                    'categories.id',
                    $school->getCategoryId()
                )
            );

            $productIds = $this->productRepository
                ->searchIds(
                    $criteria,
                    $context
                )
                ->getIds();

            foreach ($productIds as $productId) {
                $detailCriteria = new Criteria();
                $detailCriteria->addAssociation(
                    'cover.media'
                );
                $detail = $this->productDetailRoute->load(
                    $productId,
                    $request,
                    $salesChannelContext,
                    $detailCriteria
                );
                $product = $detail->getProduct();
                if (isset($schoolPriceMap[$productId])) {
                    $product->addExtension(
                        'schoolPrice',
                        new ArrayEntity([
                            'price' => (float) $schoolPriceMap[$productId],
                            'formattedPrice' => number_format(
                                (float) $schoolPriceMap[$productId],
                                2
                            )
                        ])
                    );
                }
                $products[] = $product;
            }
        }

        return $this->renderStorefront(
            '@SchoolPlugin/storefront/page/index.html.twig',
            [
                'school' => $school,
                'products' => $products,
            ]
        );
    }

    #[Route(
        path: '/school-category/{categoryId}',
        name: 'frontend.school.category',
        methods: ['GET'],
        defaults: ['_routeScope' => ['storefront']]
    )]
    public function schoolCategory(
        string $categoryId
    ): Response {
        return $this->redirectToRoute(
            'frontend.navigation.page',
            [
                'navigationId' => $categoryId,
            ]
        );
    }
}