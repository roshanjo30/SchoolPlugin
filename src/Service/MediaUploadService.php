<?php declare(strict_types=1);

namespace SchoolPlugin\Service;

use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaUploadService
{
    public function __construct(
        private readonly EntityRepository $mediaRepository,
        private readonly FileSaver $fileSaver
    ) {
    }

    public function upload(
        UploadedFile $file,
        Context $context
    ): string {
        $mediaId = bin2hex(random_bytes(16));
        $this->mediaRepository->create([
            [
                'id' => $mediaId,
                'private' => false,
            ]
        ], $context);
    
        $mediaFile = new MediaFile(
            $file->getPathname(),
            $file->getClientMimeType() ?? 'application/octet-stream',
            $file->getClientOriginalExtension(),
            $file->getSize()
        );
    
        $uniqueFilename = 'school_logo_' . $mediaId;
        $this->fileSaver->persistFileToMedia(
            $mediaFile,
            $uniqueFilename,
            $mediaId,
            $context
        );
        return $mediaId;
    }
}