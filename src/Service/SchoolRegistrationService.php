<?php declare(strict_types=1);

namespace SchoolPlugin\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use SchoolPlugin\Validation\SchoolValidator;


/**
 * @internal
 */
final class SchoolRegistrationService
{
    public function __construct(
        private readonly EntityRepository $schoolRepository,
        private readonly MediaUploadService $mediaUploadService,
        private readonly SchoolValidator $schoolValidator
    ) {
    }

    public function register(
        array $data,
        ?UploadedFile $logo,
        Context $context
    ): void {
        $this->schoolValidator->validate($data);
        
 
        $this->schoolValidator->validateLogo($logo);

        $logoMediaId = null;
        if ($logo instanceof UploadedFile) {
            $logoMediaId = $this->mediaUploadService->upload(
                $logo,
                $context
            );
        }

        $this->schoolRepository->create([
            [
                'schoolName' => $data['schoolName'],
                'contactPerson' => $data['contactPerson'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'street' => $data['street'] ?? null,
                'zipcode' => $data['zipcode'] ?? null,
                'city' => $data['city'] ?? null,
                'countryId' => $data['countryId'],
                'comment' => $data['comment'] ?? null,
                'logoMediaId' => $logoMediaId,
                'status' => 'disabled',
            ]
        ], $context);
    }
}