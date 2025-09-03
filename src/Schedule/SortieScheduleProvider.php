<?php
// src/Schedule/SortieScheduleProvider.php

namespace App\Schedule;

use App\Message\SortieReminder;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
class SortieScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())->add(
        // Exécute tous les jours à 2h du matin
            RecurringMessage::cron('0 2 * * *', new SortieReminder())
        );
    }
}
