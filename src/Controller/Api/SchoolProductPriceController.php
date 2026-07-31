<?php declare(strict_types=1);

namespace SchoolPlugin\Controller\Api;

use SchoolPlugin\Core\Content\SchoolProductPrice\SchoolProductPriceEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductDetailRoute;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route(defaults: ['_routeScope' => ['api']])]
class SchoolProductPriceController
{
    public function __construct(
        private readonly EntityRepository $schoolProductPriceRepository,
        private readonly EntityRepository $schoolRepository,
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $salesChannelRepository,
        private readonly ProductDetailRoute $productDetailRoute,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory
    ) {
    }


    #[Route(
        path: '/api/_action/school-product-price/save',
        name: 'api.action.school_product_price.save',
        methods: ['POST']
    )]
    public function savePrices(
        Request $request,
        Context $context
    ): JsonResponse {

        $payload = json_decode(
            $request->getContent(),
            true
        );


        if (
            empty($payload['schoolId'])
            || empty($payload['prices'])
        ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid payload'
            ], 400);
        }



        foreach ($payload['prices'] as $price) {


            $criteria = new Criteria();


            $criteria->addFilter(
                new EqualsFilter(
                    'schoolId',
                    $payload['schoolId']
                )
            );


            $criteria->addFilter(
                new EqualsFilter(
                    'productId',
                    $price['productId']
                )
            );


            $criteria->addFilter(
                new EqualsFilter(
                    'productVersionId',
                    Defaults::LIVE_VERSION
                )
            );



            /** @var SchoolProductPriceEntity|null $existing */
            $existing = $this->schoolProductPriceRepository
                ->search(
                    $criteria,
                    $context
                )
                ->first();



            if ($existing) {


                $this->schoolProductPriceRepository->update(
                    [
                        [
                            'id' => $existing->getId(),

                            'price' => (float)$price['price']
                        ]
                    ],
                    $context
                );


            } else {


                $this->schoolProductPriceRepository->create(
                    [
                        [

                            'id' => Uuid::randomHex(),

                            'schoolId' => $payload['schoolId'],

                            'productId' => $price['productId'],

                            'productVersionId' => Defaults::LIVE_VERSION,

                            'price' => (float)$price['price'],

                            'active' => true
                        ]
                    ],
                    $context
                );

            }

        }



        return new JsonResponse([
            'success' => true
        ]);
    }




    #[Route(
        path: '/api/_action/school-product-price/{schoolId}',
        name: 'api.action.school_product_price.load',
        methods: ['GET']
    )]
    public function loadPrices(
        string $schoolId,
        Context $context
    ): JsonResponse {


        $criteria = new Criteria();


        $criteria->addFilter(
            new EqualsFilter(
                'schoolId',
                $schoolId
            )
        );


        $criteria->addFilter(
            new EqualsFilter(
                'productVersionId',
                Defaults::LIVE_VERSION
            )
        );



        $prices = $this->schoolProductPriceRepository
            ->search(
                $criteria,
                $context
            )
            ->getEntities();



        $result = [];



        /** @var SchoolProductPriceEntity $price */
        foreach ($prices as $price) {


            $result[] = [

                'id' => $price->getId(),

                'schoolId' => $price->getSchoolId(),

                'productId' => $price->getProductId(),

                'price' => $price->getPrice(),

                'active' => $price->isActive()

            ];

        }


        return new JsonResponse($result);

    }





    #[Route(
        path: '/api/_action/school-product-price/products/{schoolId}',
        name: 'api.action.school_product_price.products',
        methods: ['GET']
    )]
    public function getProducts(
        string $schoolId,
        Context $context
    ): JsonResponse {


        $school = $this->schoolRepository
            ->search(
                new Criteria([$schoolId]),
                $context
            )
            ->first();



        if (!$school || !$school->getCategoryId()) {

            return new JsonResponse([]);

        }




        $salesChannelCriteria = new Criteria();


        $salesChannelCriteria->addFilter(
            new EqualsFilter(
                'typeId',
                Defaults::SALES_CHANNEL_TYPE_STOREFRONT
            )
        );


        $salesChannelCriteria->setLimit(1);



        $salesChannel = $this->salesChannelRepository
            ->search(
                $salesChannelCriteria,
                $context
            )
            ->first();



        if (!$salesChannel) {

            return new JsonResponse([]);

        }




        $salesChannelContext =
            $this->salesChannelContextFactory->create(
                '',
                $salesChannel->getId()
            );




        $existingPriceCriteria = new Criteria();


        $existingPriceCriteria->addFilter(
            new EqualsFilter(
                'schoolId',
                $schoolId
            )
        );


        $existingPriceCriteria->addFilter(
            new EqualsFilter(
                'productVersionId',
                Defaults::LIVE_VERSION
            )
        );



        $existingPrices =
            $this->schoolProductPriceRepository
                ->search(
                    $existingPriceCriteria,
                    $context
                )
                ->getEntities();



        $savedPriceByProductId = [];



        /** @var SchoolProductPriceEntity $existingPrice */
        foreach ($existingPrices as $existingPrice) {

            $savedPriceByProductId[
                $existingPrice->getProductId()
            ] = $existingPrice->getPrice();

        }





        $criteria = new Criteria();


        $criteria->addFilter(
            new EqualsFilter(
                'categories.id',
                $school->getCategoryId()
            )
        );


        $products = $this->productRepository
            ->search(
                $criteria,
                $context
            )
            ->getEntities();




        $result = [];



        /** @var ProductEntity $product */
        foreach ($products as $product) {



            $detail = $this->productDetailRoute->load(
                $product->getId(),
                new Request(),
                $salesChannelContext,
                new Criteria()
            );



            $detailProduct = $detail->getProduct();


            $calculatedPrice =
                $detailProduct
                    ->getCalculatedPrice()
                    ?->getUnitPrice() ?? 0;



            $result[] = [

                'id' => $product->getId(),

                'name' =>
                    $product->getTranslation('name')
                    ?? $product->getName(),

                'productNumber' =>
                    $product->getProductNumber(),


                'defaultPrice' =>
                    $calculatedPrice,


                'schoolPrice' =>
                    $savedPriceByProductId[$product->getId()]
                    ?? $calculatedPrice

            ];

        }



        return new JsonResponse($result);

    }
}