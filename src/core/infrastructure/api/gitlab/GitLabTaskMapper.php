<?php

namespace core\infrastructure\api\gitlab;

use core\application\ExternalTaskDTO;
use core\domain\TaskMapperInterface;

class GitLabTaskMapper implements TaskMapperInterface
{
    public function mapExternalToDTO(object|array $rawTask): ?ExternalTaskDTO
    {
        if (!isset($rawTask->iid, $rawTask->title, $rawTask->state)) {
            return null;
        }

        return new ExternalTaskDTO(
            id: $rawTask->iid,
            name: $rawTask->title,
            isCompleted: $rawTask->state !== 'opened',
            sourceSystem: 'gitlab'
        );
    }
}