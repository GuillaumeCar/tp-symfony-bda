<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function addPost(Request $request, SluggerInterface $slugger): Response
    {
        $postForm = $this->createForm(PostType::class);

        $postForm->handleRequest($request);

        if ($postForm->isSubmitted() && $postForm->isValid()) {
            /** @var Post $post */
            $post = $postForm->getData();

            $filepath = $postForm->get('filepath')->getData();

            if ($filepath) {
                $originalFilename = pathinfo($filepath->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$filepath->guessExtension();

                try {
                    $filepath->move(
                        $this->getParameter('assets_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    dump($e->getMessage());
                }
                $post->setFilepath($newFilename);
            }

            $post->setDate(new DateTime());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('post/postSubmit.html.twig', [
            'postForm' => $postForm->createView(),
        ]);
    }
}
