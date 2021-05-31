<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $type = ['journal', 'podcast', 'newsletter'];

        for ($i = 0; $i < 20; $i++) {
            $post = new Post();
            $post->setTitle($faker->sentence(3));
            $post->setDescription($faker->text());
            $post->setDate($faker->dateTime());
            $post->setType($type[floor(rand(0,2))]);
            if('podcast'===$post->getType()){
                $post->setSoundcloud("https://soundcloud.com/discover/sets/personalized-tracks::thedd1710:1042424293");
            } else {
                $post->setSoundcloud("");
            }
            $post->setFilepath('.');
            for($j = 0; $j < rand(0,7); $j++){
                $comment = new Comment();

                $author = new User();
                $author->setUsername($faker->userName());
                $author->setName($faker->firstName());
                $author->setSurname($faker->lastName());
                $author->setRoles(['user']);
                $author->setPassword($faker->password());
                $manager->persist($author);

                $comment->setAuthor($author);
                $comment->setCreatedAt($faker->dateTime());
                $comment->setComment($faker->text(230));
                $comment->setPost($post);
                $manager->persist($comment);
            }
            $manager->flush();
            $manager->persist($post);
        }
    }
}
