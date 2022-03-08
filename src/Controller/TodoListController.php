<?php

namespace App\Controller;

use App\Entity\TodoListItem;
use App\Form\TodoListItemType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TodoListController extends AbstractController
{
    #[Route('', name: 'app_todo_list')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(TodoListItemType::class, new TodoListItem());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd(
                $form->getData()
            );
        }

        return $this->render('todo_list/index.html.twig', [
            'controller_name' => 'TodoListController',
            'form' => $form->createView(),
        ]);
    }
}
