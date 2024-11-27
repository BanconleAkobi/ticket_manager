<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Monolog\DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{

    private UserPasswordHasherInterface $hasher;
    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher = $hasher;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $roles = [
            'ROLE_USER',
            'ROLE_TECH_SUPPORT',
            'ROLE_ADMIN',
        ];
        for($i = 0; $i < 10; $i++){
            $user = new User();

            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setEmail($faker->email());
            $user->setRoles([$roles[array_rand($roles)]]);
            $password = $this->hasher->hashPassword($user, 'commonpass');
            $user->setPassword($password);
            $createdAt = $faker->dateTimeBetween('-1 month', 'now');

            $user->setCreatedAt(DateTimeImmutable::createFromMutable($createdAt));

            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getGroups(): array{
        return ['users'];
    }
}
