<?php

namespace core\domain;

use core\exception\AppException;

/**
 *
 */
class Task
{
    /**
     * @var bool
     */
    protected bool $changed = false;
    /**
     * @var int|null
     */
    protected ?int $parentId = null;

    /**
     * @param int $id
     * @param string $externalId
     * @param string $name
     * @param int $completed
     * @param string $system
     * @throws AppException
     */
    public function __construct(
        protected int    $id,
        protected string $externalId,
        protected string $name,
        protected int    $completed,
        protected string $system,
    )
    {
        $this->validate('name', $name);
        $this->validate('externalId', $externalId);
        $this->validate('system', $system);
    }

    /**
     * @return int[]
     */
    private function constraints(): array
    {
        return [
            'name' => 1000,
            'externalId' => 100,
            'system' => 100
        ];
    }

    /**
     * @param string $parameter
     * @param mixed $value
     * @return void
     * @throws AppException
     */
    private function validate(string $parameter, mixed $value): void
    {
        if (!isset($this->constraints()[$parameter])) {
            return;
        }

        $constraint = $this->constraints()[$parameter];
        if (strlen($value) > $constraint) {
            throw new AppException('Validation fails: field ' . $parameter . 'should be shorter than ' . $constraint . 'symbols');
        }
    }

    /**
     * @return string
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * @return string
     */
    public function getSystem(): string
    {
        return $this->system;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCompleted(): int
    {
        return $this->completed;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ? int
    {
        return $this->parentId;
    }

    /**
     * @param int|null $id
     * @return void
     */
    public function setParentId(? int $id): void
    {
        $this->parentId = $id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setLocalId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param bool $completed
     * @return void
     */
    public function setCompleted(bool $completed): void
    {
        $this->completed = $completed;
        $this->changed = true;
    }

    /**
     * @param string $name
     * @return void
     * @throws AppException
     */
    public function setName(string $name): void
    {
        $this->validate('name', $name);
        $this->name = $name;
        $this->changed = true;
    }

    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return $this->changed;
    }

    /**
     * @return void
     */
    public function setUpdated(): void
    {
        $this->changed = false;
    }
}
