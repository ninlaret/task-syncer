<?php

namespace core\domain\collection;

use core\domain\TaskApiUpdateInterface;

class TaskUpdatersCollection
{
    /** @var TaskApiUpdateInterface[] */
    private array $updaters;

    public function __construct(array $updaters)
    {
        foreach ($updaters as $updater) {
            if (!$updater instanceof TaskApiUpdateInterface) {
                throw new \InvalidArgumentException('Must be instances of TaskApiUpdateInterface');
            }
        }
        $this->updaters = $updaters;
    }

    public function getUpdaters(): array
    {
        return $this->updaters;
    }
}