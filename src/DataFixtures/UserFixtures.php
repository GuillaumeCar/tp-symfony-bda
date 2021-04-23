<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('toto');
        $user->setName('toto');
        $user->setSurname('toto');
        $user->setRoles(['admin', 'user']);
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
            $user,
            'toto'
            )
        );
        $manager->persist($user);

        $user2 = new User();
        $user2->setUsername('titi');
        $user2->setName('titi');
        $user2->setSurname('titi');
        $user2->setRoles(['user']);
        $user2->setPassword(
            $this->passwordEncoder->encodePassword(
                $user2,
                'titi'
            )
        );
        $manager->persist($user2);

        $manager->flush();
        }
}
