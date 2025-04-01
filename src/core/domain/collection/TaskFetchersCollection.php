<?php

namespace core\domain\collection;

use core\domain\TaskApiFetchInterface;

class TaskFetchersCollection
{
    /** @var TaskApiFetchInterface[] */
    private array $fetchers;

    public function __construct(array $fetchers)
    {
        foreach ($fetchers as $fetcher) {
            if (!$fetcher instanceof TaskApiFetchInterface) {
                throw new \InvalidArgumentException('Must be instances of TaskApiFetchInterface');
            }
        }
        $this->fetchers = $fetchers;
    }

    public function getFetchers(): array
    {
        return $this->fetchers;
    }
}