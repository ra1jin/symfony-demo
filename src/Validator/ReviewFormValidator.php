<?php

namespace App\Validator;

use Assert\Assert;

class ReviewFormValidator
{
    public static function create($data) {
        $assert = Assert::lazy();

        $assert->that($data)->keyExists("message", "Le champs message n'est pas définis", "message");
        $assert->that($data)->keyExists("rating", "Le champs rating n'est pas définis", "rating");

        $assert->that($data['message'])->notEmpty("Le champs message est vide");
        $assert->that($data['rating'])->notEmpty("Le champs rating est vide");

        $assert->verifyNow();
        return true;
    }

    public static function response($data) {
        $assert = Assert::lazy();

        $assert->that($data)->keyExists("message", "Le champs message n'est pas définis", "message");

        $assert->that($data['message'])->notEmpty("Le champs message est vide");

        $assert->verifyNow();
        return true;
    }
}