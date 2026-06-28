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
        return __('enums.crm_timeline_entry_type.'.$this->value);
    }
}
