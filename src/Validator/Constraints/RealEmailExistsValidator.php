<?php
// src/Validator/Constraints/RealEmailExistsValidator.php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RealEmailExistsValidator extends ConstraintValidator
{
    public function __construct(private HttpClientInterface $httpClient) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof RealEmailExists) {
            return;
        }

        // Skip empty values — let NotBlank/Email handle those
        if (null === $value || '' === $value) {
            return;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://rapid-email-verifier.fly.dev/api/validate', [
                'query' => ['email' => $value],
                'timeout' => 5, // don't block forever if API is slow
            ]);

            $data = $response->toArray();

            // Adjust this key based on what the API actually returns
        
            $isValid = isset($data['status']) && $data['status'] === 'VALID';

            if (!$isValid) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $value)
                    ->addViolation();
            }

        } catch (\Throwable $e) {
            // If API is down or times out, log it but don't block the user
            error_log('Email verification API error: ' . $e->getMessage());
            // Fail open: allow registration to proceed
        }
    }
}
