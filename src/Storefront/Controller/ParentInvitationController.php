<?php declare(strict_types=1);

namespace SchoolPlugin\Storefront\Controller;

use SchoolPlugin\Event\SchoolParentInvitedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ParentInvitationController extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $schoolRepository,
        private readonly EntityRepository $schoolParentInvitationRepository,
        private readonly EntityRepository $salesChannelRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route(
        path: '/school/{schoolId}/invite-parent',
        name: 'frontend.school.invite.parent',
        methods: ['POST']
    )]
    public function inviteParent(
        string $schoolId,
        Request $request,
        SalesChannelContext $salesChannelContext
    ): RedirectResponse {

        $context = $salesChannelContext->getContext();
        $school = $this->schoolRepository
            ->search(new Criteria([$schoolId]), $context)
            ->first();
        if ($school === null) {
            $this->addFlash(
                'danger',
                $this->trans('schoolPlugin.storefront.invitation.error.schoolNotFound')
            );
            return $this->redirect(
                $request->headers->get('referer') ?? '/'
            );
        }

        $parentName = trim((string) $request->request->get('parentName'));
        $email = trim((string) $request->request->get('email'));
        if ($parentName === '') {
            $this->addFlash(
                'danger',
                $this->trans('schoolPlugin.storefront.invitation.error.parentNameRequired')
            );
            return $this->redirect(
                $request->headers->get('referer') ?? '/'
            );
        }

        if ($email === '') {
            $this->addFlash(
                'danger',
                $this->trans('schoolPlugin.storefront.invitation.error.emailRequired')
            );
            return $this->redirect(
                $request->headers->get('referer') ?? '/'
            );
        }

        $token = bin2hex(random_bytes(32));
        $this->schoolParentInvitationRepository->create([
            [
                'id' => Uuid::randomHex(),
                'schoolId' => $schoolId,
                'parentName' => $parentName,
                'email' => $email,
                'token' => $token,
            ],
        ], $context);

        $salesChannelId = $salesChannelContext->getSalesChannelId();
        $salesChannel = $this->salesChannelRepository
            ->search(new Criteria([$salesChannelId]), $context)
            ->first();
            $domain = '';
            if ($salesChannel !== null && $salesChannel->getDomains()) {
                $domain = rtrim(
                    $salesChannel->getDomains()->first()->getUrl(),
                    '/'
                );
            }
            if ($domain === '') {
                $domain = $request->getSchemeAndHttpHost();
            }
            
        $parentCategoryId = $school->getParentCategoryId();
        if (!$parentCategoryId) {
            $this->addFlash(
                'danger',
                $this->trans('schoolPlugin.storefront.invitation.error.categoryNotFound')
            );
            return $this->redirect(
                $request->headers->get('referer') ?? '/'
            );
        }

        $categoryUrl = $domain . '/school/invite/' . $token;
        $mailRecipients = new MailRecipientStruct([
            $email => $parentName,
        ]);

        $this->eventDispatcher->dispatch(
            new SchoolParentInvitedEvent(
                $context,
                $salesChannelId,
                $mailRecipients,
                $school->getSchoolName(),
                $categoryUrl,
                $parentName
            ),
            SchoolParentInvitedEvent::EVENT_NAME
        );

        $this->addFlash(
            'success',
            $this->trans(
                'schoolPlugin.storefront.invitation.success.sent',
                ['%name%' => $parentName]
            )
        );

        return $this->redirect(
            $request->headers->get('referer') ?? '/'
        );
    }
}