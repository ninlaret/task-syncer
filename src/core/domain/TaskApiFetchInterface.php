<?php

namespace core\domain;

interface TaskApiFetchInterface
{
    public function fetchTasks(): array;
}