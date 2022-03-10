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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TodoListController extends AbstractController
{
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
                ]
            ]
        )->getForm();

        $existingTodoListItems = $this->todoListRepository->first()->getTodoListItems()->toArray();

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

        if ($hasFormBeenSubmitted === true) {
            $this->addFlash(
                'notice',
                sprintf(
                    'Task %s successfully.',
                    $request->get('_method') === 'DELETE' ? 'deleted' : 'saved'
                )
            );

            return $this->redirect($request->getUri());
        }

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
                )->setMethod('DELETE')->getForm()->createView(),
                $existingTodoListItems
            ),
        ]);
    }

    /**
     * @param TodoListItem[] $items
     * @param Request $request
     * @return bool
     * @throws Exception
     * @link https://stackoverflow.com/a/36557060/8472578
     */
    private function handleSubmittedForms(array $items, Request $request): bool
    {
        foreach ($items as $item) {
            $formName = $item->getId() === null ? 'todo_list_item_form_0' : 'todo_list_item_form_' . $item->getId();

            $form = $this->formFactory->createNamedBuilder(
                $formName,
                TodoListItemType::class,
                $item
            )->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() === false) {
                continue;
            }

            if ($form->isValid() === false) {
                $errorMessage = 'An error occurred while attempting to create your task.';

                foreach ($form->getErrors(true) as $error) {
                    $errorMessage = $error->getMessage();

                    break;
                }

                throw new Exception($errorMessage);
            }

            /** @var TodoListItem $item */
            $item = $form->getData();

            if ($request->get('_method') === 'DELETE') {
                $this->entityManager->remove($item);
            } else {
                if ($item->getIsCompleted() === null) {
                    $item->setIsCompleted(false);
                }

                if ($item->getTodoList() === null) {
                    $item->setTodoList($this->todoListRepository->first() ?? new TodoList());
                }

                $this->entityManager->persist($item);
            }

            $this->entityManager->flush();

            return true;
        }

        return false;
    }
}
