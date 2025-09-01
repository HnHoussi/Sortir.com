<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SortieDateConstraint extends Constraint
{
    public string $message = "La date limite d'inscription doit être au moins 48 heures avant le début de la sortie.";

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
