<?php

namespace core\domain\entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "task_links")]
class TaskLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(name: "source_task_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Task $sourceTask;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(name: "target_task_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Task $targetTask;

    #[ORM\Column(name: "last_synced_at", type: "datetime_immutable")]
    private DateTimeImmutable $lastSyncedAt;

    public function __construct(Task $sourceTask, Task $targetTask)
    {
        $this->sourceTask = $sourceTask;
        $this->targetTask = $targetTask;
        $this->lastSyncedAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSourceTask(): Task
    {
        return $this->sourceTask;
    }

    public function getTargetTask(): Task
    {
        return $this->targetTask;
    }

    public function getLastSyncedAt(): DateTimeImmutable
    {
        return $this->lastSyncedAt;
    }

    public function updateLastSyncedAt(): void
    {
        $this->lastSyncedAt = new DateTimeImmutable();
    }
}