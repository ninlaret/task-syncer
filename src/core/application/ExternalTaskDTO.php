<?php

namespace core\application;

class ExternalTaskDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public bool $isCompleted,
        public string $sourceSystem
    ) {}
}