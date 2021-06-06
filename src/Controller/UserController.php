<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /** @var UserRepository */
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/profile", name="app_user_edit")
     */
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $userForm = $this->createForm(UserType::class);

        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()) {
            /** @var User $updatedUser */
            $updatedUser = $userForm->getData();

            $user->setName($updatedUser->getName());
            $user->setSurname($updatedUser->getSurname());

            $manager = $this->getDoctrine()->getManager();

            $manager->flush();

            $this->addFlash('success', 'Profil mis à jour !');

            return $this->redirectToRoute('app_user_edit');
        }

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'userForm'        => $userForm->createView(),
            'user'            => $user
        ]);
    }

    /**
     * @Route("/users", name="app_user_list")
     */
    public function list(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $users = $this->userRepository->findAll();

        return $this->render('user/list.html.twig', [
            'controller_name' => 'UserController',
            'user'            => $user,
            'users'           => $users
        ]);
    }
}
