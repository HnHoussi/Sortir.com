<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Entity\Sortie;

class SortieDateValidator extends ConstraintValidator
{
public function validate(mixed $value, Constraint $constraint): void
{
if (!$value instanceof Sortie) {
return;
}

if (null === $value->getStartDatetime() || null === $value->getRegistrationDeadline()) {
return;
}

$diff = $value->getStartDatetime()->getTimestamp() - $value->getRegistrationDeadline()->getTimestamp();

// 48 heures = 48 * 60 * 60 secondes
if ($diff < 172800) {
$this->context->buildViolation($constraint->message)->addViolation();
}
}
}
