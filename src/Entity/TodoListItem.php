<?php

namespace App\Entity;

use App\Repository\TodoListItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TodoListItemRepository::class)]
class TodoListItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: TodoList::class, inversedBy: 'title')]
    #[ORM\JoinColumn(nullable: false)]
    private $todo_list;

    #[ORM\Column(type: 'boolean')]
    private $is_completed;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTodoList(): ?TodoList
    {
        return $this->todo_list;
    }

    public function setTodoList(?TodoList $todo_list): self
    {
        $this->todo_list = $todo_list;

        return $this;
    }

    public function getIsCompleted(): ?bool
    {
        return $this->is_completed;
    }

    public function setIsCompleted(bool $is_completed): self
    {
        $this->is_completed = $is_completed;

        return $this;
    }
}