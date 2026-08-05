<?php declare(strict_types=1);

namespace SchoolPlugin\Validation;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class SchoolValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }


    public function validate(array $data): void
    {
        $constraints = new Assert\Collection([
            'fields' => [
                'schoolName' => [
                    new Assert\NotBlank(),
                ],
                'contactPerson' => [
                    new Assert\NotBlank(),
                ],
                'email' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
                'countryId' => [
                    new Assert\NotBlank(),
                ],
            ],
            'allowExtraFields' => true,
        ]);

        $violations = $this->validator->validate(
            $data,
            $constraints
        );


        if ($violations->count() > 0) {
            throw new ValidationFailedException(
                $data,
                $violations
            );
        }
    }


    public function validateLogo(?UploadedFile $logo): void
    {
        if ($logo === null) {
            $violations = new ConstraintViolationList();
        
            $violations->add(
                new ConstraintViolation(
                    'schoolPlugin.validation.logo',
                    null,
                    [],
                    null,
                    'logo',
                    null
                )
            );
        
            throw new ValidationFailedException(
                [],
                $violations
            );
        }

        $violations = new ConstraintViolationList();

        $extension = strtolower(
            $logo->getClientOriginalExtension()
        );


        $allowedExtensions = [
            'jpg',
            'jpeg',
            'png',
            'webp',
            'svg',
        ];


        if (!in_array($extension, $allowedExtensions, true)) {

            $violations->add(
                new ConstraintViolation(
                    'schoolPlugin.validation.invalidLogo',
                    null,
                    [],
                    $logo,
                    'logo',
                    null
                )
            );
        }


        if ($extension === 'svg') {

            $svg = file_get_contents(
                $logo->getRealPath()
            );


            if ($svg === false) {

                $violations->add(
                    new ConstraintViolation(
                        'schoolPlugin.validation.invalidLogo',
                        null,
                        [],
                        $logo,
                        'logo',
                        null
                    )
                );

            } elseif (preg_match(
                '/<script\b|on[a-z]+\s*=|foreignObject|javascript:|<iframe|<object|<embed/i',
                $svg
            )) {

                $violations->add(
                    new ConstraintViolation(
                        'schoolPlugin.validation.logoActiveContent',
                        null,
                        [],
                        $logo,
                        'logo',
                        null
                    )
                );
            }
        }


        if ($logo->getSize() > 5 * 1024 * 1024) {

            $violations->add(
                new ConstraintViolation(
                    'schoolPlugin.validation.logoTooLarge',
                    null,
                    [],
                    $logo,
                    'logo',
                    null
                )
            );
        }


        if ($violations->count() > 0) {

            throw new ValidationFailedException(
                ['logo' => $logo],
                $violations
            );
        }
    }
}