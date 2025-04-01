<?php

namespace core\infrastructure\api\notion;

use core\application\ExternalTaskDTO;
use core\domain\TaskMapperInterface;

class NotionTaskMapper implements TaskMapperInterface
{
    public function mapExternalToDTO(object|array $rawTask): ?ExternalTaskDTO
    {
        if (!isset($rawTask->id, $rawTask->properties->Name->title[0]->plain_text, $rawTask->properties->Done->checkbox)) {
            return null;
        }

        return new ExternalTaskDTO(
            id: $rawTask->id,
            name: $rawTask->properties->Name->title[0]->plain_text,
            isCompleted: $rawTask->properties->Done->checkbox,
            sourceSystem: 'notion'
        );
    }
}