<?php

namespace core\domain\collection;

use core\domain\TaskMapperInterface;

class TaskMappersCollection
{
    /** @var TaskMapperInterface[] */
    private array $mappers;

    public function __construct(array $mappers)
    {
        foreach ($mappers as $mapper) {
            if (!$mapper instanceof TaskMapperInterface) {
                throw new \InvalidArgumentException('Must be instances of TaskMapperInterface');
            }
        }
        $this->mappers = $mappers;
    }

    public function getMappers(): array
    {
        return $this->mappers;
    }
}