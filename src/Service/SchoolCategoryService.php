<?php declare(strict_types=1);

namespace SchoolPlugin\Service;

use SchoolPlugin\Core\Content\School\SchoolEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class SchoolCategoryService
{
    public const SCHOOLS_ROOT_CATEGORY_NAME = 'Schools';

    public function __construct(
        private readonly EntityRepository $categoryRepository,
        private readonly EntityRepository $schoolRepository
    ) {
    }

    public function createCategory(
        SchoolEntity $school,
        Context $context
    ): string {
        if ($school->getCategoryId()) {
    
            $this->categoryRepository->update([[
                'id' => $school->getCategoryId(),
                'active' => true,
            ]], $context);
    
            return $school->getCategoryId();
        }
    
    
        $rootCategoryId = $this->ensureRootCategory($context);
    
        /*
         * Create school category
         */
        $categoryId = Uuid::randomHex();
    
        $this->categoryRepository->create([[
            'id' => $categoryId,
            'parentId' => $rootCategoryId,
            'name' => $school->getSchoolName(),
            'active' => true,
            'visible' => false,
            'mediaId' => $school->getLogoMediaId(),
        ]], $context);
    
    
        /*
         * Create Parents sub-category
         */
        $parentCategoryId = Uuid::randomHex();
    
        $this->categoryRepository->create([[
            'id' => $parentCategoryId,
            'parentId' => $categoryId,
            'name' => 'Parents',
            'active' => true,
            'visible' => false,
        ]], $context);
    
    
        /*
         * Save both categories
         */
        $this->schoolRepository->update([[
            'id' => $school->getId(),
            'categoryId' => $categoryId,
            'parentCategoryId' => $parentCategoryId,
        ]], $context);
    
    
        return $categoryId;
    }

    public function deactivateCategory(
        string $categoryId,
        Context $context
    ): void {
        if (!$categoryId) {
            return;
        }
    
        $this->categoryRepository->update([[
            'id' => $categoryId,
            'active' => false,
        ]], $context);
    }


    private function ensureRootCategory(Context $context): string
    {
        $criteria = new Criteria();

        $criteria->addFilter(
            new EqualsFilter(
                'name',
                self::SCHOOLS_ROOT_CATEGORY_NAME
            )
        );

        $criteria->addFilter(
            new EqualsFilter(
                'parentId',
                null
            )
        );

        $criteria->setLimit(1);


        $existing = $this->categoryRepository
            ->search($criteria, $context)
            ->first();


        if ($existing) {
            return $existing->getId();
        }


        $rootId = Uuid::randomHex();


        $this->categoryRepository->create([[
            'id' => $rootId,
            'name' => self::SCHOOLS_ROOT_CATEGORY_NAME,
            'active' => true,
            'visible' => false,
        ]], $context);


        return $rootId;
    }
}