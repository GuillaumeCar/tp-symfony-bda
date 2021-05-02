<?php

namespace App\Controller;

use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DatalistController extends AbstractController
{
    /**
     * @Route("/datalist", name="datalist")
     */
    public function index(): Response
    {
        return $this->render('datalist/index.html.twig', [
            'controller_name' => 'DatalistController',
        ]);
    }

    /**
     * @Route("/addPost", name="app_add_post")
     */
    public function addPost(): Response
    {
        $postForm = $this->createForm(PostType::class);

        return $this->render('post/postSubmit.html.twig', [
            'postForm' => $postForm->createView(),
        ]);
    }



}
