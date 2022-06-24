<?php

namespace App\Validator;

use Assert\Assert;

class SecurityFormValidator
{
    public static function login($data) {
        $assert = Assert::lazy();

        $assert->that($data)->keyExists("email", "Le champs email n'est pas définis", "email");
        $assert->that($data)->keyExists("password", "Le champs password n'est pas définis", "password");

        $assert->that($data['email'])->notEmpty("Le champs email est vide");
        $assert->that($data['password'])->notEmpty("Le champs password est vide");

        $assert->verifyNow();
        return true;
    }

    public static function subscribe($data) {
        $assert = Assert::lazy();

        $assert->that($data)->keyExists("email", "Le champs email n'est pas définis", "email");
        $assert->that($data)->keyExists("role", "Le champs role n'est pas définis", "role");
        $assert->that($data)->keyExists("password", "Le champs mot de passe n'est pas définis", "password");
        $assert->that($data)->keyExists("password_confirm", "Le champs confirmation de mot de passe n'est pas définis", "password_confirm");
        $assert->that($data)->keyExists("firstname", "Le champs prénom n'est pas définis", "firstname");
        $assert->that($data)->keyExists("lastname", "Le champs nom n'est pas définis", "lastname");
        $assert->that($data)->keyExists("zipcode", "Le champs code postal n'est pas définis", "zipcode");
        $assert->that($data)->keyExists("cityname", "Le champs ville n'est pas définis", "cityname");
        $assert->verifyNow();

        $assert->that($data['email'])->notEmpty("Le champs email est vide");
        $assert->that($data['role'])->notEmpty("Le champs role est vide");
        $assert->that($data['password'])->notEmpty("Le champs mot de passe est vide");
        $assert->that($data['password_confirm'])->notEmpty("Le champs confirmation de mot de passe est vide");
        $assert->that($data['firstname'])->notEmpty("Le champs prénom est vide");
        $assert->that($data['lastname'])->notEmpty("Le champs nom est vide");
        $assert->that($data['zipcode'])->notEmpty("Le champs code postal est vide");
        $assert->that($data['cityname'])->notEmpty("Le champs ville est vide");
        $assert->verifyNow();

        return true;
    }
}