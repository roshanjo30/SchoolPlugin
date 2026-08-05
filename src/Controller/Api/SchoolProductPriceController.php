<?php declare(strict_types=1);

namespace SchoolPlugin\Controller\Api;

use SchoolPlugin\Core\Content\SchoolProductPrice\SchoolProductPriceEntity;
use SchoolPlugin\Validation\SchoolProductPriceValidator;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[Route(defaults: ['_routeScope' => ['api']])]
class SchoolProductPriceController
{
    public function __construct(
        private readonly EntityRepository $schoolProductPriceRepository,
        private readonly EntityRepository $schoolRepository,
        private readonly EntityRepository $productRepository,
        private readonly SchoolProductPriceValidator $schoolProductPriceValidator
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
            !is_array($payload) ||
            empty($payload['schoolId']) ||
            empty($payload['prices'])
        ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid payload'
            ], 400);
        }


        /**
         * Validate school exists
         */
        $school = $this->schoolRepository
            ->search(
                new Criteria([$payload['schoolId']]),
                $context
            )
            ->first();

        if ($school === null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'School not found'
            ], 404);
        }


        $productIds = array_filter(
            array_column(
                $payload['prices'],
                'productId'
            )
        );

        $productCriteria = new Criteria($productIds);

        $productMap = [];

        $loadedProducts = $this->productRepository
            ->search(
                $productCriteria,
                $context
            )
            ->getEntities();

        /** @var ProductEntity $product */
        foreach ($loadedProducts as $product) {
            $productMap[$product->getId()] = $product;
        }


        /**
         * Load existing prices
         */
        $criteria = new Criteria();

        $criteria->addFilter(
            new EqualsFilter(
                'schoolId',
                $payload['schoolId']
            )
        );

        if ($productIds !== []) {
            $criteria->addFilter(
                new EqualsAnyFilter(
                    'productId',
                    $productIds
                )
            );
        }

        $criteria->addFilter(
            new EqualsFilter(
                'productVersionId',
                Defaults::LIVE_VERSION
            )
        );


        $existingEntities = $this->schoolProductPriceRepository
            ->search(
                $criteria,
                $context
            )
            ->getEntities();


        $existingMap = [];

        /** @var SchoolProductPriceEntity $entity */
        foreach ($existingEntities as $entity) {
            $existingMap[$entity->getProductId()] = $entity;
        }



        foreach ($payload['prices'] as $price) {

            $productId = $price['productId'] ?? null;


            /**
             * Validate request data
             */
            $validationData = [
                'schoolId' => $payload['schoolId'],
                'productId' => $productId,
                'price' => $price['price'] ?? null,
            ];


            try {

                $this->schoolProductPriceValidator->validate(
                    $validationData
                );

            } catch (ValidationFailedException $exception) {

                return new JsonResponse([
                    'success' => false,
                    'errors' => array_map(
                        static fn ($violation) =>
                            $violation->getMessage(),
                        iterator_to_array(
                            $exception->getViolations()
                        )
                    )
                ], 400);
            }

            $product = $productMap[$productId] ?? null;

            if ($product === null) {

                return new JsonResponse([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);

            }

            $existing = $existingMap[$productId] ?? null;

            if ($existing instanceof SchoolProductPriceEntity) {

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
                            'productId' => $productId,
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


        $existingPrices = $this->schoolProductPriceRepository
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


        $criteria->addAssociation('prices');
        $criteria->addAssociation('tax');
        $criteria->addAssociation('cover.media');



        $products = $this->productRepository
            ->search(
                $criteria,
                $context
            )
            ->getEntities();



        $result = [];


        /** @var ProductEntity $product */
        foreach ($products as $product) {

            $defaultPrice = 0;


            if ($product->getPrice()) {

                $defaultPrice =
                    $product->getPrice()
                        ->first()
                        ?->getGross()
                    ?? 0;

            }


            $result[] = [

                'id' => $product->getId(),

                'name' =>
                    $product->getTranslation('name')
                    ?? $product->getName(),

                'productNumber' =>
                    $product->getProductNumber(),

                'defaultPrice' =>
                    $defaultPrice,

                'schoolPrice' =>
                    $savedPriceByProductId[$product->getId()]
                    ?? $defaultPrice
            ];

        }


        return new JsonResponse($result);
    }
}