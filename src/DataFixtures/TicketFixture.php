<?php

namespace App\DataFixtures;

use App\Entity\Ticket;
use App\Enum\Priority;
use App\Enum\Status;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Monolog\DateTimeImmutable;

class TicketFixture extends Fixture implements FixtureGroupInterface
{
    private $userRepository;
    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }
    public function load(ObjectManager $manager): void
    {
        $status = Status::cases();
        dd($this->userRepository->findAll());

        dd($this->userRepository->findBy([
            'roles' => ["ROLE_USER"]
        ]));
        $faker = Factory::create();
        for($i = 0; $i < 60; $i++){
            $ticket = new Ticket();
            $ticket->setTitle($faker->title());
            $ticket->setDescription($faker->text());
            $ticket->setStatus($faker->randomElement($status));
            $ticket->setPriority($faker->randomElement($priority));
            $ticket->setAssignedTo($faker->randomElement());
            $ticket->setCreatedBy($faker->randomElement());
            $date = $faker->dateTimeBetween('-1 month', 'now');
            $createdAt = DateTimeImmutable::createFromMutable($date);
            $ticket->setCreatedAt($createdAt);
            $ticket->setDeadline($createdAt->modify('+3 days'));
            $manager->persist($ticket);
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
    public static function getGroups(): array{
        return [
            'tickets'
        ];
    }
}
