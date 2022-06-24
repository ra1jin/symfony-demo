<?php

namespace App\Validator;

use Assert\Assert;

class RestaurantFormValidator
{
    public static function create($data) {
        $assert = Assert::lazy();

        $assert->that($data)->keyExists("name", "Le champs nom n'est pas définis", "name");
        $assert->that($data)->keyExists("description", "Le champs nom n'est pas définis", "description");
        $assert->that($data)->keyExists("zipcode", "Le champs code postal n'est pas définis", "zipcode");
        $assert->that($data)->keyExists("cityname", "Le champs ville n'est pas définis", "cityname");

        $assert->that($data['name'])->notEmpty("Le champs nom est vide");
        $assert->that($data['description'])->notEmpty("Le champs description est vide");
        $assert->that($data['zipcode'])->notEmpty("Le champs code postal est vide");
        $assert->that($data['cityname'])->notEmpty("Le champs ville est vide");

        $assert->verifyNow();
        return true;
    }
}