<?php declare(strict_types=1);

namespace SchoolPlugin\Controller\Api;

use SchoolPlugin\Core\Content\School\SchoolEntity;
use SchoolPlugin\Event\SchoolApprovedEvent;
use SchoolPlugin\Service\SchoolCategoryService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Shopware\Core\Defaults;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: ['_routeScope' => ['api']])]
class SchoolAdminController
{
    public function __construct(
        private readonly EntityRepository $schoolRepository,
        private readonly EntityRepository $salesChannelRepository,
        private readonly SchoolCategoryService $schoolCategoryService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route(
        path: '/api/_action/school/{id}/approve',
        name: 'api.action.school.approve',
        methods: ['POST']  
    )]
    public function approve(string $id, Context $context): JsonResponse
    {
        /** @var SchoolEntity|null $school */
        $school = $this->schoolRepository
            ->search(new Criteria([$id]), $context)
            ->first();

        if (!$school instanceof SchoolEntity) {
            return new JsonResponse([
                'error' => 'School not found'
            ], 404);
        }

        $categoryId = $school->getCategoryId();
        if (!$categoryId) {
            $categoryId = $this->schoolCategoryService
                ->createCategory($school, $context);
            $school->setCategoryId($categoryId);
        
        } else {
            $this->schoolCategoryService
                ->createCategory($school, $context);
        }

        $this->schoolRepository->update([
            [
                'id' => $id,
                'status' => 'approved',
            ]
        ], $context);

        $criteria = new Criteria();
        $criteria->addAssociation('domains');
        $criteria->addFilter(new EqualsFilter('typeId', \Shopware\Core\Defaults::SALES_CHANNEL_TYPE_STOREFRONT));
        $criteria->setLimit(1);

        $salesChannel = $this->salesChannelRepository->search($criteria, $context)->first();

        if (!$salesChannel) {
            return new JsonResponse([
                'error' => 'No storefront sales channel found',
            ], 500);
        }
        
        $domain = $salesChannel->getDomains()?->first()?->getUrl();
        $categoryUrl = rtrim($domain ?? '', '/') . '/school-category/' . $categoryId;
        
        $this->eventDispatcher->dispatch(
            new SchoolApprovedEvent(
                $context,
                $salesChannel->getId(),
                new MailRecipientStruct([
                    $school->getEmail() => $school->getContactPerson(),
                ]),
                $school,
                $categoryUrl
            )
        );

        return new JsonResponse([
            'success' => true,
            'categoryId' => $categoryId,
        ]);
    }

    #[Route(
        path: '/api/_action/school/{id}/disable',
        name: 'api.action.school.disable',
        methods: ['POST']
        
    )]
    public function disable(
        string $id,
        Context $context
    ): JsonResponse {
        /** @var SchoolEntity|null $school */
        $school = $this->schoolRepository
            ->search(new Criteria([$id]), $context)
            ->first();

        if (!$school instanceof SchoolEntity) {
            return new JsonResponse([
                'error' => 'School not found'
            ], 404);
        }

        $categoryId = $school->getCategoryId();
        if ($categoryId) {
            $this->schoolCategoryService
                ->deactivateCategory($categoryId, $context);
        }

        $this->schoolRepository->update(
            [[
                'id' => $id,
                'status' => 'disabled',
            ]],
            $context
        );
        return new JsonResponse([
            'success' => true,
        ]);
    }
}
