<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $adminRole = new UserRole();
        $adminRole->setName(UserRole::ROLE_ADMIN );

        $userRole = new UserRole();
        $userRole->setName(UserRole::ROLE_USER);

        $adminUser = new User();
        $adminUser->setLogin('testAdmin')
            ->setPass('adminpass')
            ->setPhone('12345678')
            ->setRole($adminRole);
        $adminRole->setUser($adminUser);

        $regularUser = new User();
        $regularUser->setLogin('testUser')
            ->setPass('userpass')
            ->setPhone('23456789')
            ->setRole($userRole);
        $userRole->setUser($regularUser);

        $manager->persist($adminUser);
        $manager->persist($regularUser);

        $manager->persist($adminRole);
        $manager->persist($userRole);

        $manager->flush();
    }
}
