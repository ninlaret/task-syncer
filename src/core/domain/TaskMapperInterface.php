<?php

namespace core\domain;

use core\application\ExternalTaskDTO;

interface TaskMapperInterface
{
    public function mapExternalToDTO(object|array $rawTask): ?ExternalTaskDTO;
}