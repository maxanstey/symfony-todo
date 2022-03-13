<?php

namespace App\Tests;

use App\Entity\TodoList;
use App\Entity\TodoListItem;
use App\Repository\TodoListItemRepository;
use App\Repository\TodoListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TodoListTest extends KernelTestCase
{
    private TodoListRepository $todoListRepository;
    private TodoListItemRepository $todoListItemRepository;

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testThatListsCanBeCreatedAndRemoved(): void
    {
        $todoList = new TodoList();

        $this->todoListRepository->add($todoList);

        $this->assertTrue(null !== $todoList->getId());

        $this->todoListRepository->remove($todoList);

        $this->assertTrue(null === $todoList->getId());
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testThatItemsCanBeCreatedAndRemovedFromLists(): void
    {
        $todoList = new TodoList();

        $todoListItem = new TodoListItem();
        $todoListItem->setTodoList($todoList);
        $todoListItem->setTitle('Test title');
        $todoListItem->setIsCompleted(false);

        $todoList->addTodoListItem($todoListItem);

        $this->todoListRepository->add($todoList);

        $this->assertTrue(null !== $todoListItem->getId());

        $this->assertTrue($todoList->getTodoListItems()->first()->getId() === $todoListItem->getId());

        $todoList->removeTodoListItem($todoListItem);

        $this->todoListRepository->add($todoList);

        $this->assertTrue(null === $todoListItem->getId());
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testThatItemsCanBeCreatedAndRemoved(): void
    {
        $todoList = new TodoList();

        $this->todoListRepository->add($todoList);

        $todoListItem = new TodoListItem();
        $todoListItem->setTodoList($todoList);
        $todoListItem->setTitle('Test title');
        $todoListItem->setIsCompleted(false);

        $todoList->addTodoListItem($todoListItem);

        $this->todoListItemRepository->add($todoListItem);

        $this->assertTrue(null !== $todoListItem->getId());

        $this->assertTrue($todoList->getTodoListItems()->first()->getId() === $todoListItem->getId());

        $this->todoListItemRepository->remove($todoListItem);

        $this->assertTrue(null === $todoListItem->getId());
    }

    protected function setUp(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        /** @var TodoListRepository $todoListRepository */
        $todoListRepository = $entityManager->getRepository(TodoList::class);
        $this->todoListRepository = $todoListRepository;

        /** @var TodoListItemRepository $todoListItemRepository */
        $todoListItemRepository = $entityManager->getRepository(TodoListItem::class);
        $this->todoListItemRepository = $todoListItemRepository;

        parent::setUp();
    }
}
