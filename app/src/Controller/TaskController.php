<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/task')]
class TaskController extends AbstractController
{
    // --- Ćwiczenie 1: Lista rekordów ---
    #[Route('', name: 'task_index', methods: ['GET'])]
    public function index(TaskRepository $repository): Response
    {
        // Wyciągamy wszystkie zadania z bazy
        $tasks = $repository->findAll();

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks
        ]);
    }

    // --- Ćwiczenie 2: Pojedynczy rekord (ParamConverter) ---
    #[Route('/{id}', name: 'task_view', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    public function view(Task $task): Response
    {
        // Zauważ brak TaskRepository! Symfony samo wstrzyknęło gotowy obiekt $task
        // na podstawie {id} z adresu URL. To jest właśnie ParamConverter.
        return $this->render('task/view.html.twig', [
            'task' => $task
        ]);
    }
}
