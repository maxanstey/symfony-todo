<?php

namespace App\Entity;

use App\Repository\TodoListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TodoListRepository::class)]
class TodoList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToMany(
        mappedBy: 'todo_list',
        targetEntity: TodoListItem::class,
        cascade: [
            'persist',
        ],
        orphanRemoval: true,
    )]
    private $todoListItems;

    public function __construct()
    {
        $this->todoListItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, TodoListItem>
     */
    public function getTodoListItems(): Collection
    {
        return $this->todoListItems;
    }

    public function addTodoListItem(TodoListItem $todoListItem): self
    {
        if (!$this->todoListItems->contains($todoListItem)) {
            $this->todoListItems[] = $todoListItem;
            $todoListItem->setTodoList($this);
        }

        return $this;
    }

    public function removeTodoListItem(TodoListItem $todoListItem): self
    {
        if ($this->todoListItems->removeElement($todoListItem)) {
            // set the owning side to null (unless already changed)
            if ($todoListItem->getTodoList() === $this) {
                $todoListItem->setTodoList(null);
            }
        }

        return $this;
    }
}
