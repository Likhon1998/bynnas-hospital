<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class NotificationSeenStore
{
    private const int TTL_SECONDS = 60 * 60 * 24 * 14;

    private const int MAX_IDS = 500;

    /**
     * @return list<string>
     */
    public function ids(User $user): array
    {
        $ids = Cache::get($this->key($user), []);

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_map(
            static fn (mixed $id): string => (string) $id,
            $ids,
        )));
    }

    /**
     * @param  list<string>  $ids
     */
    public function mark(User $user, array $ids): void
    {
        $clean = [];

        foreach ($ids as $id) {
            if (! is_string($id)) {
                continue;
            }

            $trimmed = trim($id);

            if ($trimmed === '' || strlen($trimmed) > 120) {
                continue;
            }

            $clean[] = $trimmed;
        }

        $clean = array_values(array_unique($clean));

        if ($clean === []) {
            return;
        }

        $merged = array_values(array_unique([...$this->ids($user), ...$clean]));

        if (count($merged) > self::MAX_IDS) {
            $merged = array_slice($merged, -self::MAX_IDS);
        }

        Cache::put($this->key($user), $merged, self::TTL_SECONDS);
    }

    private function key(User $user): string
    {
        return 'admin.notifications.seen.'.$user->getAuthIdentifier();
    }
}
