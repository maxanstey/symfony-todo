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

    #[ORM\OneToMany(mappedBy: 'todo_list', targetEntity: TodoListItem::class, orphanRemoval: true)]
    private $title;

    public function __construct()
    {
        $this->title = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, TodoListItem>
     */
    public function getTitle(): Collection
    {
        return $this->title;
    }

    public function addTitle(TodoListItem $title): self
    {
        if (!$this->title->contains($title)) {
            $this->title[] = $title;
            $title->setTodoList($this);
        }

        return $this;
    }

    public function removeTitle(TodoListItem $title): self
    {
        if ($this->title->removeElement($title)) {
            // set the owning side to null (unless already changed)
            if ($title->getTodoList() === $this) {
                $title->setTodoList(null);
            }
        }

        return $this;
    }
}
