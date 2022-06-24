<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Review;
use App\Entity\City;
use App\Entity\Restaurant;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $numCity = 10;
        $numUser = 2;
        $numRestaurant = 100;
        $numReview = 100;

        for ($i = 0; $i < $numCity; $i++) {
            $city = new City();
            $city->setName($faker->city);
            $city->setZipcode($faker->postcode);
            $manager->persist($city);
            $this->addReference(City::class . '_' . $i, $city);
        }

        for ($i = 0; $i < $numUser; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setRoles(['MEMBER_ROLE']);
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'test'));
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setCity($this->getReference(City::class . '_' . random_int(0, $numCity - 1)));
            $manager->persist($user);
            $this->addReference(User::class . '_' . $i, $user);
        }

        for ($i = 0; $i < $numRestaurant; $i++) {
            $rest = new Restaurant();
            $rest->setName($faker->name);
            $rest->setDescription($faker->text);
            $rest->setCreatedAt($faker->dateTime);
            $rest->setCity($this->getReference(City::class . '_' . random_int(0, $numCity - 1)));
            $rest->setManager($this->getReference(User::class . '_' . random_int(0, $numUser - 1)));
            $manager->persist($rest);
            $this->addReference(Restaurant::class . '_' . $i, $rest);
        }

        for ($i = 0; $i < $numReview; $i++) {
            $review = new Review();
            $review->setMessage($faker->text);
            $review->setRating(random_int(0, 5));
            $review->setCreatedAt($faker->dateTime);
            $review->setAuthor($this->getReference(User::class . '_' . random_int(0, $numUser - 1)));
            $review->setRestaurant($this->getReference(Restaurant::class . '_' . random_int(0, $numRestaurant - 1)));
            $manager->persist($review);
            $this->addReference(Review::class . '_' . $i, $review);
        }

        $manager->flush();
    }
}
