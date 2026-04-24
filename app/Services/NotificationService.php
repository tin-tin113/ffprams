<?php

namespace App\Services;

use App\Models\DistributionEvent;
use Illuminate\Support\Collection;

class NotificationService
{
    public function getAttentionRequiredEvents(): Collection
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();
        
        $alerts = collect();

        // 1. Activation Required (Pending today/tomorrow)
        $activationRequired = DistributionEvent::where('status', 'Pending')
            ->whereBetween('distribution_date', [$today, $tomorrow])
            ->get();
        foreach ($activationRequired as $event) {
            $alerts->push([
                'id' => "activation-{$event->id}",
                'event_id' => $event->id,
                'type' => 'activation',
                'priority' => 'high',
                'icon' => 'bi-lightning-fill',
                'title' => 'Activation Required',
                'message' => "Event '{$event->name}' is scheduled for " . ($event->distribution_date->isToday() ? 'today' : 'tomorrow') . ".",
                'url' => route('distribution-events.show', $event->id),
                'time' => $event->distribution_date->diffForHumans(),
            ]);
        }

        // 2. Overdue Processing (Ongoing but past date)
        $overdueProcessing = DistributionEvent::where('status', 'Ongoing')
            ->where('distribution_date', '<', $today)
            ->get();
        foreach ($overdueProcessing as $event) {
            $alerts->push([
                'id' => "overdue-{$event->id}",
                'event_id' => $event->id,
                'type' => 'overdue',
                'priority' => 'medium',
                'icon' => 'bi-clock-history',
                'title' => 'Overdue Processing',
                'message' => "Event '{$event->name}' was scheduled for {$event->distribution_date->format('M d')}.",
                'url' => route('distribution-events.show', $event->id),
                'time' => $event->distribution_date->diffForHumans(),
            ]);
        }

        // 3. Delayed Start (Pending but past date)
        $delayedStart = DistributionEvent::where('status', 'Pending')
            ->where('distribution_date', '<', $today)
            ->get();
        foreach ($delayedStart as $event) {
            $alerts->push([
                'id' => "delayed-{$event->id}",
                'event_id' => $event->id,
                'type' => 'delayed',
                'priority' => 'high',
                'icon' => 'bi-exclamation-triangle-fill',
                'title' => 'Delayed Activation',
                'message' => "Event '{$event->name}' is OVERDUE for activation.",
                'url' => route('distribution-events.show', $event->id),
                'time' => $event->distribution_date->diffForHumans(),
            ]);
        }

        // 4. Unmarked Allocations (Past events with unrecorded outcomes)
        $pastEvents = DistributionEvent::where('distribution_date', '<', $today)
            ->where('distribution_date', '>=', now()->subDays(30))
            ->whereIn('status', ['Ongoing', 'Completed'])
            ->get();

        foreach ($pastEvents as $event) {
            $unmarked = $event->unmarkedAllocationsCount();
            if ($unmarked > 0) {
                $alerts->push([
                    'id' => "unmarked-{$event->id}",
                    'event_id' => $event->id,
                    'type' => 'unmarked',
                    'priority' => 'medium',
                    'icon' => 'bi-person-exclamation',
                    'title' => 'Unmarked Allocations',
                    'message' => "Event '{$event->name}' has {$unmarked} unmarked beneficiaries.",
                    'url' => route('distribution-events.show', $event->id),
                    'time' => $event->distribution_date->diffForHumans(),
                ]);
            }
        }

        return $alerts->sortByDesc(function ($alert) {
            return $alert['priority'] === 'high' ? 2 : 1;
        });
    }
}
