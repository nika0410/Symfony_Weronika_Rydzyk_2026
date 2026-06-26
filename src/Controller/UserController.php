<?php

/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController.
 */
#[Route('/user')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UserServiceInterface $userService User service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'user_index', methods: ['GET'])]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->userService->getPaginatedList($page);

        return $this->render('user/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * View action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'user_view', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    public function view(User $user): Response
    {
        return $this->render('user/view.html.twig', ['user' => $user]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'user_create', methods: ['GET', 'POST'], priority: 10)]
    public function create(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['require_password' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $this->userService->save($user, $plainPassword);

            $this->addFlash('success', $this->translator->trans('message.created_successfully'));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'user_edit', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(
            UserType::class,
            $user,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('user_edit', ['id' => $user->getId()]),
                'require_password' => false,
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $this->userService->save($user, $plainPassword);

            $this->addFlash('success', $this->translator->trans('message.edited_successfully'));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'user_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    public function delete(Request $request, User $user): Response
    {
        $form = $this->createForm(FormType::class, $user, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('user_delete', ['id' => $user->getId()]),
            'validation_groups' => false,
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() && $request->isMethod('DELETE')) {
            $form->submit($request->request->all($form->getName()));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->delete($user);

            $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/delete.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    /**
     * Promote action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/promote', name: 'user_promote', requirements: ['id' => '[1-9]\d*'], methods: ['POST'])]
    public function promote(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid('promote'.$user->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('user_index');
        }

        $this->userService->promoteToAdmin($user);

        $this->addFlash('success', $this->translator->trans('message.promoted_successfully'));

        return $this->redirectToRoute('user_index');
    }

    /**
     * Demote action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/demote', name: 'user_demote', requirements: ['id' => '[1-9]\d*'], methods: ['POST'])]
    public function demote(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid('demote'.$user->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('user_index');
        }
        if ($user === $this->getUser()) {
            $this->addFlash('warning', $this->translator->trans('message.cannot_demote_self'));

            return $this->redirectToRoute('user_index');
        }

        $this->userService->demoteFromAdmin($user);

        $this->addFlash('success', $this->translator->trans('message.demoted_successfully'));

        return $this->redirectToRoute('user_index');
    }




}
