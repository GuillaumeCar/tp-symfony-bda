<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Composer\Repository\RepositoryFactory;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class DatalistController extends AbstractController
{
    /**
     * @Route("/datalist/{type}", name="datalist")
     */
    public function index(string $type, PostRepository $postRepository): Response
    {
        $list = $postRepository->findBy(array('type' => $type));

        return $this->render('datalist/index.html.twig', [
            'controller_name' => 'DatalistController',
            'datalist' => $list
        ]);
    }

    /**
     * @Route("/datalist/{type}/{id}", name="datalistDetails")
     */
    public function show(string $type, string $id, PostRepository $postRepository): Response
    {
        $post = $postRepository->find($id);

        if ($post && $post->getType() == $type) {
            return $this->render('datalist/details.html.twig', [
                'post' => $post,
                'type' => $type
            ]);
        }
        $posts = $postRepository->findLatestByType();
        return $this->render('home/index.html.twig', [
            'message' => "Cette édition n'existe pas.",
            'posts' => $posts
        ]);

    }

    /**
     * @Route("/addPost", name="app_add_post")
     * @throws TransportExceptionInterface
     */
    public function addPost(Request $request, SluggerInterface $slugger, MailerInterface $mailer): Response
    {
        $postForm = $this->createForm(PostType::class);

        $postForm->handleRequest($request);

        if ($postForm->isSubmitted() && $postForm->isValid()) {
            /** @var Post $post */
            $post = $postForm->getData();

            if ('podcast' !== $post->getType()) {
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
            } else {
                $post->setFilepath($post->getSoundcloud());
            }

            $post->setDate(new DateTime());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($post);
            $manager->flush();

            $this->broadcastMail($post, $mailer);

            return $this->redirectToRoute('home');
        }

        return $this->render('post/postSubmit.html.twig', [
            'postForm' => $postForm->createView(),
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function broadcastMail(Post $post, MailerInterface $mailer)
    {
        $emailTemplate = sprintf('email/%s.html.twig', $post->getType());

        $email = (new TemplatedEmail())
            ->from('fabien@example.com')
            ->to('ryan@example.com')
            ->subject($post->getTitle())
            ->htmlTemplate($emailTemplate)
            ->context([
                'type' => $post->getType(),
                'titre' => $post->getTitle(),
                'description' => $post->getDescription(),
                'soundcloud' => $post->getSoundcloud(),
            ]);

        if ('podcast' !== $post->getType()) {
            $email->attachFromPath(
                sprintf('%s/%s', $this->getAssetDirPath(), $post->getFilepath()),
                $post->getFilepath()
            );
        }

        $mailer->send($email);
    }

    private function getAssetDirPath()
    {
        return $this->getParameter('assets_dir');
    }
}
