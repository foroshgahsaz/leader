<?php

namespace App\Enums;

enum CrmTimelineEntryType: string
{
    case Activity = 'activity';
    case CrmActivity = 'crm_activity';
    case Note = 'note';
    case Task = 'task';
    case Meeting = 'meeting';
    case File = 'file';
    case StageChange = 'stage_change';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Activity => 'Activity',
            self::CrmActivity => 'Logged Activity',
            self::Note => 'Note',
            self::Task => 'Task',
            self::Meeting => 'Meeting',
            self::File => 'File',
            self::StageChange => 'Stage Change',
            self::System => 'System',
        };
    }
}
