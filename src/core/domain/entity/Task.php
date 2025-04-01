<?php

namespace core\domain\entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "tasks")]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "name", type: "string", length: 255)]
    private string $name;

    #[ORM\Column(name: "is_completed", type: "boolean")]
    private bool $isCompleted = false;

    #[ORM\Column(name: "external_system", type: "string", length: 100)]
    private string $externalSystem;

    #[ORM\Column(name: "external_id", type: "string", length: 255)]
    private string $externalId;

    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime_immutable")]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(name: "is_deleted", type: "boolean", nullable: true, options: ["default" => false])]
    private ?bool $isDeleted = false;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): void
    {
        $this->isCompleted = $isCompleted;
    }

    public function getExternalSystem(): string
    {
        return $this->externalSystem;
    }

    public function setExternalSystem(string $externalSystem): void
    {
        $this->externalSystem = $externalSystem;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateTimestamp(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isNew(): bool
    {
        return empty($this->id);
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted === true;
    }

    public function setDeleted(): bool
    {
        return $this->isDeleted = true;
    }
}