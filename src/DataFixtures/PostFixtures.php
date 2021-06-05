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

        $author = new User();
        $author->setUsername($faker->userName());
        $author->setName($faker->firstName());
        $author->setSurname($faker->lastName());
        $author->setRoles(['user']);
        $author->setPassword($faker->password());
        $manager->persist($author);

        for ($i = 0; $i < 20; $i++) {
            $post = new Post();
            $post->setTitle($faker->sentence(3));
            $post->setDescription($faker->text());
            $post->setDate($faker->dateTime());
            $post->setType($type[floor(rand(0,2))]);
            if('podcast' === $post->getType()){
                $post->setSoundcloud("<iframe width='100%' height='166' scrolling='no' frameborder='no' allow='autoplay' src='https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/772717807&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true'></iframe><div style='font-size: 10px; color: #cccccc;line-break: anywhere;word-break: normal;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; font-family: Interstate,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Garuda,Verdana,Tahoma,sans-serif;font-weight: 100;'><a href='https://soundcloud.com/bda-ig2i' title='BDA IG2I' target='_blank' style='color: #cccccc; text-decoration: none;'>BDA IG2I</a> · <a href='https://soundcloud.com/bda-ig2i/le-potecast-episode-2' title='Le Potecast - Épisode 2' target='_blank' style='color: #cccccc; text-decoration: none;'>Le Potecast - Épisode 2</a></div>");
            } else {
                $post->setSoundcloud("fichier-60adec7c61407.pdf");
            }
            $post->setFilepath('.');
            for($j = 0; $j < rand(0,7); $j++){
                $comment = new Comment();

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
