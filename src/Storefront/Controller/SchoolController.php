<?php declare(strict_types=1);

namespace SchoolPlugin\Storefront\Controller;

use SchoolPlugin\Service\SchoolRegistrationService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @internal
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
final class SchoolController extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $countryRepository,
        private readonly SchoolRegistrationService $schoolRegistrationService
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

        return $this->renderStorefront(
            '@SchoolPlugin/storefront/page/school/index.html.twig',
            [
                'countries' => $this->getCountries($context),
                'success' => $this->getRegistrationSuccess($request),
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
        
        $debug = null;

        try {

            $this->schoolRegistrationService->register(
                $request->request->all(),
                $request->files->get('logo'),
                $context->getContext()
            );


            $request->getSession()->set(
                'school_registration_success',
                true
            );


            return $this->redirectToRoute(
                'frontend.school.registration'
            );


        } catch (ValidationFailedException $exception) {

            $messages = [];
        
            foreach ($exception->getViolations() as $violation) {
                $messages[] = [
                    'property' => $violation->getPropertyPath(),
                    'message'  => $violation->getMessage(),
                ];
            }
        
            return $this->renderStorefront(
                '@SchoolPlugin/storefront/page/school/index.html.twig',
                [
                    'countries'  => $this->getCountries($context),
                    'formData'   => $request->request->all(),
                    'violations' => $exception->getViolations(),
                    'debug'      => $debug,
                    'messages'   => $messages,
                ]
            );
        


        } catch (\Throwable $exception) {

            $message = $exception->getMessage();
        
            $logoErrors = [
                'schoolPlugin.validation.invalidLogo',
                'schoolPlugin.validation.logoTooLarge',
                'schoolPlugin.validation.logoActiveContent',
            ];
        
            if (in_array($message, $logoErrors, true)) {
                $error = $this->trans($message);
            } else {
                $error = $this->trans(
                    'schoolPlugin.validation.generalError'
                );
        
            }
        
            return $this->renderStorefront(
                '@SchoolPlugin/storefront/page/school/index.html.twig',
                [
                    'countries' => $this->getCountries($context),
                    'formData' => $request->request->all(),
                    'error' => $error,
                ]
            );
        }
    }


    private function getCountries(
        SalesChannelContext $context
    ) {
        $criteria = new Criteria();

        $criteria->addFilter(
            new EqualsFilter(
                'active',
                true
            )
        );

        $criteria->addSorting(
            new FieldSorting('position')
        );

        $criteria->addSorting(
            new FieldSorting('name')
        );


        return $this->countryRepository
            ->search(
                $criteria,
                $context->getContext()
            )
            ->getEntities();
    }


    private function getRegistrationSuccess(
        Request $request
    ): bool {

        $success = $request->getSession()
            ->get(
                'school_registration_success',
                false
            );


        $request->getSession()
            ->remove(
                'school_registration_success'
            );


        return $success;
    }
}