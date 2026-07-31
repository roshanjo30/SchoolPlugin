<?php declare(strict_types=1);

namespace SchoolPlugin\Storefront;

use Symfony\Component\HttpFoundation\RequestStack;

class SchoolContext
{
    private ?string $schoolId = null;


    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }


    public function setSchoolId(string $schoolId): void
    {
        $session = $this->requestStack
            ->getSession();

        $session->set(
            'schoolId',
            $schoolId
        );
    }


    public function getSchoolId(): ?string
    {
        return $this->requestStack
            ->getSession()
            ->get('schoolId');
    }
}