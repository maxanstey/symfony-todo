<?php

namespace App\Controller;

use App\Entity\TodoList;
use App\Entity\TodoListItem;
use App\Form\TodoListItemType;
use App\Repository\TodoListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TodoListController extends AbstractController
{
    private TodoListRepository $todoListRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory
    ) {
        /** @var TodoListRepository $todoListRepository */
        $todoListRepository = $this->entityManager->getRepository(TodoList::class);

        $this->todoListRepository = $todoListRepository;
    }

    #[Route('', name: 'app_todo_list')]
    public function index(Request $request): Response
    {
        $form = $this->formFactory->createNamedBuilder(
            'todo_list_item_form_0',
            TodoListItemType::class,
            new TodoListItem(),
            [
                'attr' => [
                    'class' => 'todo_list_item_form',
                ]
            ]
        )->getForm();

        $existingTodoListItems = $this->todoListRepository->first()->getTodoListItems()->toArray();

        $this->handleSubmittedForms(
            [
                new TodoListItem(),
                ...$existingTodoListItems,
            ],
            $request
        );

        // TODO: handle form errors?
        // TODO: return $this->redirect($request->getUri()); if submitted

        return $this->render('todo_list/index.html.twig', [
            'controller_name' => 'TodoListController',
            'form' => $form->createView(),
            'items' => array_map(
                fn (TodoListItem $item) => $this->formFactory->createNamedBuilder(
                    'todo_list_item_form_' . $item->getId(),
                    TodoListItemType::class,
                    $item,
                    [
                        'attr' => [
                            'class' => 'todo_list_item_form',
                        ]
                    ]
                )->getForm()->createView(),
                $existingTodoListItems
            ),
        ]);
    }

    /**
     * @param TodoListItem[] $todoListItems
     * @param Request $request
     * @return void
     * @link https://stackoverflow.com/a/36557060/8472578
     */
    private function handleSubmittedForms(array $todoListItems, Request $request): void
    {
        foreach ($todoListItems as $item) {
            $formName = $item->getId() === null ? 'todo_list_item_form_0' : 'todo_list_item_form_' . $item->getId();

            $form = $this->formFactory->createNamedBuilder(
                $formName,
                TodoListItemType::class,
                $item
            )->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() === false || $form->isValid() === false) {
                continue;
            }

            /** @var TodoListItem $todoListItem */
            $todoListItem = $form->getData();

            if ($todoListItem->getIsCompleted() === null) {
                $todoListItem->setIsCompleted(false);
            }

            if ($todoListItem->getTodoList() === null) {
                $todoListItem->setTodoList($this->todoListRepository->first() ?? new TodoList());
            }

            $this->entityManager->persist($todoListItem);
        }

        $this->entityManager->flush();
    }
}
