<?php

namespace App\Controller;

use App\Entity\TodoList;
use App\Entity\TodoListItem;
use App\Form\TodoListItemType;
use App\Repository\TodoListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TodoListController extends AbstractController
{
    public const CREATE_MESSAGE = 'Task created successfully.';
    public const DELETE_MESSAGE = 'Task deleted successfully.';
    private TodoListRepository $todoListRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory,
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
                ],
            ]
        )->getForm();

        $existingTodoListItems = $this->todoListRepository->findFirst()?->getTodoListItems()->toArray() ?: [];

        try {
            $hasFormBeenSubmitted = $this->handleSubmittedForms(
                [
                    new TodoListItem(),
                    ...$existingTodoListItems,
                ],
                $request
            );
        } catch (Exception $exception) {
            $this->addFlash(
                'notice',
                $exception->getMessage()
            );

            return $this->redirect($request->getUri());
        }

        if (true === $hasFormBeenSubmitted) {
            $this->addFlash(
                'notice',
                'DELETE' === $request->get('_method') ? self::DELETE_MESSAGE : self::CREATE_MESSAGE
            );

            return $this->redirect($request->getUri());
        }

        return $this->render('todo_list/index.html.twig', [
            'controller_name' => 'TodoListController',
            'form' => $form->createView(),
            'items' => array_map(
                fn (TodoListItem $item): FormView => $this->formFactory->createNamedBuilder(
                    'todo_list_item_form_'.$item->getId(),
                    TodoListItemType::class,
                    $item,
                    [
                        'attr' => [
                            'class' => 'todo_list_item_form',
                        ],
                    ]
                )->setMethod('DELETE')->getForm()->createView(),
                $existingTodoListItems
            ),
        ]);
    }

    /**
     * @param TodoListItem[] $items
     *
     * @throws Exception
     *
     * @see https://stackoverflow.com/a/36557060/8472578
     */
    private function handleSubmittedForms(array $items, Request $request): bool
    {
        foreach ($items as $item) {
            $formName = null === $item->getId() ? 'todo_list_item_form_0' : 'todo_list_item_form_'.$item->getId();

            $form = $this->formFactory->createNamedBuilder(
                $formName,
                TodoListItemType::class,
                $item
            )->getForm();

            $form->handleRequest($request);

            if (false === $form->isSubmitted()) {
                continue;
            }

            if (false === $form->isValid()) {
                $errorMessage = 'An error occurred while attempting to create your task.';

                foreach ($form->getErrors(true) as $error) {
                    $errorMessage = $error->getMessage();

                    break;
                }

                throw new Exception($errorMessage);
            }

            /** @var TodoListItem $item */
            $item = $form->getData();

            if ('DELETE' === $request->get('_method')) {
                $this->entityManager->remove($item);
            } else {
                if (null === $item->getIsCompleted()) {
                    $item->setIsCompleted(false);
                }

                if (null === $item->getTodoList()) {
                    $todoList = $this->todoListRepository->findFirst();

                    if ($todoList === null) {
                        $todoList = new TodoList();

                        $this->entityManager->persist($todoList);
                    }

                    $item->setTodoList($todoList);
                }

                $this->entityManager->persist($item);
            }

            $this->entityManager->flush();

            return true;
        }

        return false;
    }
}
