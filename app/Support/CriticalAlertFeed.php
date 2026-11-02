<?php

namespace App\Support;

use App\Models\User;
use App\Models\WorkItem;
use Illuminate\Support\Collection;

class CriticalAlertFeed
{
    public function __construct(private WorkInbox $inbox) {}

    /**
     * @return array{alerts: Collection<int, array{id: string, tone: string, title: string, detail: string, href: string, time: string, read: bool}>, counts: array{beds: int, pending: int, unpaid: int, messages: int, labs: int}, total: int, ids: list<string>}
     */
    public function for(?User $user, int $limit = WorkInbox::RECENT_NOTIFICATIONS): array
    {
        if ($user === null) {
            return [
                'alerts' => Collection::make(),
                'counts' => ['beds' => 0, 'pending' => 0, 'unpaid' => 0, 'messages' => 0, 'labs' => 0],
                'total' => 0,
                'ids' => [],
            ];
        }

        $this->inbox->syncFor($user);
        $alerts = $this->inbox->notificationsFor($user, $limit);
        $counts = $this->inbox->notificationCounts($user);

        return [
            'alerts' => $alerts,
            'counts' => $counts,
            'total' => $this->inbox->unreadNotificationCount($user),
            'ids' => $alerts->pluck('id')->all(),
        ];
    }

    /**
     * @return array{alerts: Collection<int, array{id: string, tone: string, title: string, detail: string, href: string, time: string}>, counts: array{beds: int, pending: int, unpaid: int, messages: int, labs: int}, total: int, ids: list<string>}
     */
    public function all(int $limit = 30): array
    {
        $this->inbox->syncAll();

        $items = WorkItem::query()
            ->open()
            ->where('is_notification', true)
            ->orderByRaw('case when is_critical = 1 then 0 else 1 end')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $alerts = $items->map(fn (WorkItem $item): array => $this->inbox->toAlert($item))->values();

        return [
            'alerts' => $alerts,
            'counts' => [
                'beds' => $items->whereIn('kind', ['cleaning', 'exception'])->count(),
                'pending' => $items->whereIn('kind', ['pending_web', 'arrival'])->count(),
                'unpaid' => $items->where('kind', 'unpaid')->count(),
                'messages' => $items->where('kind', 'message')->count(),
                'labs' => $items->where('kind', 'critical')->count(),
            ],
            'total' => $alerts->count(),
            'ids' => $alerts->pluck('id')->all(),
        ];
    }
}
