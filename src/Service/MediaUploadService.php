<?php declare(strict_types=1);

namespace SchoolPlugin\Service;

use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @internal
 */
final class MediaUploadService
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
    
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );
    
    
        $mimeMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
    
    
        if (!isset($mimeMap[$extension])) {
            throw new \RuntimeException(
                'schoolPlugin.validation.invalidLogo'
            );
        }
    
    
        $mime = $mimeMap[$extension];
    
    
        /*
         * SVG security check
         */
        if ($extension === 'svg') {
    
            $svg = file_get_contents(
                $file->getRealPath()
            );
    
            if ($svg === false) {
                throw new \RuntimeException(
                    'schoolPlugin.validation.invalidLogo'
                );
            }
    
    
            if (
                preg_match(
                    '/<script\b|on[a-z]+\s*=|foreignObject|javascript:|<iframe|<object|<embed/i',
                    $svg
                )
            ) {
                throw new \RuntimeException(
                    'schoolPlugin.validation.logoActiveContent'
                );
            }
        }
    
    
        if ($file->getSize() > 5 * 1024 * 1024) {
    
            throw new \RuntimeException(
                'schoolPlugin.validation.logoTooLarge'
            );
        }
    
    
        $mediaId = bin2hex(
            random_bytes(16)
        );
    
    
        $this->mediaRepository->create(
            [
                [
                    'id' => $mediaId,
                    'private' => false,
                ]
            ],
            $context
        );
    
    
        $mediaFile = new MediaFile(
            $file->getPathname(),
            $mime,
            $extension,
            $file->getSize()
        );
    
    
        $uniqueFilename = 'school_logo_' . $mediaId;
    
    
        try {
    
            $this->fileSaver->persistFileToMedia(
                $mediaFile,
                $uniqueFilename,
                $mediaId,
                $context
            );
    
        } catch (\Throwable $e) {
    
            $this->mediaRepository->delete(
                [
                    [
                        'id' => $mediaId
                    ]
                ],
                $context
            );
    
    
            throw $e;
        }
    
    
        return $mediaId;
    }
}