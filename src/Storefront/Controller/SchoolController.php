<?php declare(strict_types=1);

namespace SchoolPlugin\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;


#[Route(defaults: ['_routeScope' => ['storefront']])]
class SchoolController extends StorefrontController
{
    public function __construct(
    private readonly EntityRepository $countryRepository,
    private readonly \SchoolPlugin\Service\SchoolRegistrationService $schoolRegistrationService
        

    ) {
    }

    #[Route(
        path: '/school-registration',
        name: 'frontend.school.registration',
        methods: ['GET']
    )]
    public function index(
        Request $request,
        SalesChannelContext $context
    ): Response {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addSorting(new FieldSorting('position'));
        $criteria->addSorting(new FieldSorting('name'));

        $countries = $this->countryRepository
            ->search($criteria, $context->getContext())
            ->getEntities();

        return $this->renderStorefront(
            '@SchoolPlugin/storefront/page/school/index.html.twig',
            [
                'countries' => $countries,
            ]
        );
    }
    #[Route(
        path: '/school-registration',
        name: 'frontend.school.registration.submit',
        methods: ['POST']
    )]
    public function submit(
        Request $request,
        SalesChannelContext $context
    ): Response {
    
        $this->schoolRegistrationService->register(
            $request->request->all(),
            $request->files->get('logo'),
            $context->getContext()
        );
    
        $this->addFlash(
            'success',
            'Your school registration has been submitted successfully.'
        );
    
        return $this->redirectToRoute(
            'frontend.school.registration'
        );
    }
}