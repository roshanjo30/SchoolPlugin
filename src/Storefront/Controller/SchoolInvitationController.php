<?php declare(strict_types=1);

namespace SchoolPlugin\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class SchoolInvitationController extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $schoolParentInvitationRepository,
        private readonly EntityRepository $schoolRepository,
        private readonly RequestStack $requestStack,
        private readonly CartService $cartService
    ) {
    }

    #[Route(
        path: '/school/invite/{token}',
        name: 'frontend.school.invitation',
        methods: ['GET']
    )]
    public function open(
        string $token,
        SalesChannelContext $salesChannelContext
    ): RedirectResponse {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'token',
                $token
            )
        );
        $criteria->addAssociation('school');
        $invitation = $this->schoolParentInvitationRepository
            ->search(
                $criteria,
                $salesChannelContext->getContext()
            )
            ->first();

        if (!$invitation) {
            throw $this->createNotFoundException();
        }
        $school = $this->schoolRepository
            ->search(
                new Criteria([$invitation->getSchoolId()]),
                $salesChannelContext->getContext()
            )
            ->first();

        if (!$school) {
            throw $this->createNotFoundException();
        }

        if ($school->getStatus() !== 'approved') {
            throw $this->createNotFoundException();
        }
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
        
        $session = $this->requestStack->getSession();
        $session->set(
            'selected_school_id',
            $school->getId()
        );
        
        $session->set(
            'school_invitation_token',
            $token
        );
        return $this->redirectToRoute(
            'frontend.school.page',
            [
                'schoolId' => $school->getId(),
            ]
        );
    }
}