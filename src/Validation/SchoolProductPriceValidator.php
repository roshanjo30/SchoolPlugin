<?php declare(strict_types=1);

namespace SchoolPlugin\Validation;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class SchoolProductPriceValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }


    public function validate(array $data): void
    {
        $violations = $this->validator->validate(
            $data,
            new Assert\Collection([
                'schoolId' => [
                    new Assert\NotBlank(),
                ],

                'productId' => [
                    new Assert\NotBlank(),
                ],

                'price' => [
                    new Assert\NotNull(),
                    new Assert\PositiveOrZero(),
                ],
            ])
        );


        if ($violations->count()) {
            throw new ValidationFailedException(
                $data,
                $violations
            );
        }
    }
}