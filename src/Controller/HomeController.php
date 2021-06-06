<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     * @throws \Doctrine\DBAL\Exception
     */
    public function index(PostRepository $postRepository): Response
    {
        try {
            $posts = $postRepository->findLatestByType();
        } catch (Exception $e) {
            dump($e->getMessage());
        }

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'user' => $this->getUser()
        ]);
    }
}
