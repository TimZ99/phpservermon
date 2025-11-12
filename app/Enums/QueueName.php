<?php

namespace App\Enums;

enum QueueName: string
{
    case CURL = 'curl';
    case NOTIFICATIONS = 'notifications';
    case MAINTENANCE = 'maintenance';
}
