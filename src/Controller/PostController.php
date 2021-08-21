<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
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

class PostController extends AbstractController
{
    /** @var PostRepository */
    protected $postRepository;

    /** @var UserRepository */
    protected $userRepository;

    public function __construct(UserRepository $userRepository, PostRepository $postRepository)
    {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * @Route("/posts/{type}", name="app_posts_type")
     */
    public function index(string $type): Response
    {
        $posts = $this->postRepository->findBy(array('type' => $type));

        if (empty($posts)) {
            $posts = $this->postRepository->findLatestByType();
            return $this->render('home/index.html.twig', [
                'message' => "Il n'existe pas d'édition pour ce type ou alors nous ne faisons pas encore de $type !",
                'posts' => $posts
            ]);
        }

        return $this->render('post/index.html.twig', [
            'controller_name' => 'DatalistController',
            'posts' => $posts,
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/posts/{type}/{id}", name="app_post_details")
     */
    public function show(string $type, string $id, Request $request): Response
    {
        $post = $this->postRepository->find($id);
        $commentForm = $this->createForm(CommentType::class);

        $commentForm->handleRequest($request);

        if($commentForm->isSubmitted() && $commentForm->isValid()){
            $comment = $commentForm->getData();

            $comment->setPost($post)
                ->setCreatedAt(new DateTime())
                ->setAuthor($this->getUser());

            $manager = $this->getDoctrine()->getManager();

            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('app_post_details', ['id' => $id, 'type' => $type ]);
        }

        if ($post && $post->getType() == $type) {
            return $this->render('post/details.html.twig', [
                'post' => $post,
                'type' => $type,
                'commentForm' => $commentForm->createView(),
                'user' => $this->getUser()
            ]);
        }
        $posts = $this->postRepository->findLatestByType();

        return $this->render('home/index.html.twig', [
            'message' => "Cette édition n'existe pas.",
            'posts' => $posts,
            'user' => $this->getUser()
        ]);

    }

    /**
     * @Route("/add", name="app_add_post")
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
            }

            $post->setDate(new DateTime());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($post);
            $manager->flush();

            $this->broadcastMail($post, $mailer);

            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/submit.html.twig', [
            'postForm' => $postForm->createView(),
            'user' => $this->getUser()
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
public function broadcastMail(Post $post, MailerInterface $mailer)
    {
        $emailTemplate = sprintf('email/%s.html.twig', $post->getType());

        $users = $this->userRepository->findAll();

        $mailingList = [];
        /** @var User $user */
        foreach($users as $user) {
            $mailingList[] = $user->getUsername();
        }

        $email = (new TemplatedEmail())
            ->from('no-reply@bda-ig2i.com')
            ->subject($post->getTitle())
            ->htmlTemplate($emailTemplate)
            ->context([
                'type' => $post->getType(),
                'titre' => $post->getTitle(),
                'description' => $post->getDescription(),
                'soundcloud' => $post->getSoundcloud(),
            ]);

        foreach ($mailingList as $userEmail) {
            $email->addTo($userEmail);
        }

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
