<?php

namespace core\infrastructure\api\habitica;

use core\application\ExternalTaskDTO;
use core\domain\TaskMapperInterface;

class HabiticaTaskMapper implements TaskMapperInterface
{
    public function mapExternalToDTO(object|array $rawTask): ?ExternalTaskDTO
    {
        if (!isset($rawTask->_id, $rawTask->text, $rawTask->completed)) {
            return null;
        }

        return new ExternalTaskDTO(
            id: $rawTask->_id,
            name: $rawTask->text,
            isCompleted: $rawTask->completed,
            sourceSystem: 'habitica'
        );
    }
}