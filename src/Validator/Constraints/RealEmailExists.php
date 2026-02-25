<?php
// src/Validator/Constraints/RealEmailExists.php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class RealEmailExists extends Constraint
{
    public string $message = 'This email address does not appear to exist. Please use a real email.';
}
