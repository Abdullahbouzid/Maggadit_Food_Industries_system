<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read-side notification logic shared by the customer web app and the API.
 *
 * The mobile app runs inside a WebView at http://127.0.0.1 where no Sanctum
 * token exists, so it cannot use the /api/customer/* endpoints. This service
 * keeps one implementation of "which notifications belong to this customer" and
 * of the read/star/archived state so both entry points agree.
 */
class CustomerNotificationService
{
    /**
     * Notifications addressed to the given customer (or broadcast to everyone).
     */
    public function queryFor(Customer $customer): Builder
    {
        $customerId = $customer->id;

        return Notification::where(function (Builder $query) use ($customerId) {
            $query->whereJsonContains('customers', 'all')
                ->orWhere('channel', 'all')
                ->orWhere(function (Builder $inner) use ($customerId) {
                    $inner->whereJsonContains('customers', (string) $customerId)
                        ->orWhereJsonContains('customers', $customerId);
                });
        })
            ->where('is_sent', true);
    }

    /**
     * All notifications for the customer, newest first.
     */
    public function allFor(Customer $customer, int $limit = 100): Collection
    {
        return $this->queryFor($customer)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Pivot rows for this customer, keyed by notification id.
     *
     * @return Collection<int, \App\Models\Notification>
     */
    public function readRowsFor(Customer $customer): Collection
    {
        return $customer->readNotifications()->get()->keyBy('id');
    }

    /**
     * Map a notification into the payload consumed by the mobile views.
     *
     * A notification counts as read only when its pivot row has read_at set, so
     * a starred-but-unread entry stays unread.
     *
     * @param  \App\Models\Notification|null  $pivot
     * @return array<string, mixed>
     */
    public function toPayload(Notification $notification, $pivot = null): array
    {
        $readAt = $pivot?->pivot?->read_at ?? null;

        return [
            'id' => $notification->id,
            'subject' => $notification->subject_ar ?: $notification->subject_en,
            'body' => $notification->body_ar ?: $notification->body_en,
            'image' => $notification->image_url ?? null,
            'icon_type' => $notification->icon_type ?? 'notification',
            'group' => $notification->group,
            'is_read' => $readAt !== null,
            'is_starred' => (bool) ($pivot?->pivot?->is_starred ?? false),
            'is_archived' => (bool) ($pivot?->pivot?->is_archived ?? false),
            'created_at' => $notification->created_at?->diffForHumans(),
            'created_at_raw' => $notification->created_at?->toIso8601String(),
        ];
    }

    /**
     * Number of notifications the customer has not read yet.
     */
    public function unreadCount(Customer $customer): int
    {
        $readIds = $this->readRowsFor($customer)
            ->filter(fn ($notification) => $notification->pivot->read_at !== null)
            ->keys();

        return $this->allFor($customer)
            ->reject(fn (Notification $notification) => $readIds->contains($notification->id))
            ->count();
    }

    /**
     * Notifications created after $lastId, for the polling endpoint.
     *
     * @return array<string, mixed>
     */
    public function checkNew(Customer $customer, int $lastId = 0): array
    {
        $pivots = $this->readRowsFor($customer);

        $notifications = $this->queryFor($customer)
            ->where('id', '>', $lastId)
            ->orderBy('id')
            ->limit(50)
            ->get();

        $newNotifications = $notifications
            ->map(fn (Notification $notification) => $this->toPayload(
                $notification,
                $pivots->get($notification->id)
            ))
            ->values();

        return [
            'success' => true,
            'new_notifications' => $newNotifications,
            'count' => $newNotifications->count(),
            'unread_count' => $this->unreadCount($customer),
        ];
    }

    /**
     * Mark one notification as read, creating the pivot row if needed.
     */
    public function markRead(Customer $customer, Notification $notification, bool $isRead = true): void
    {
        $this->upsert($customer, $notification, [
            'read_at' => $isRead ? now() : null,
        ]);
    }

    public function toggleStar(Customer $customer, Notification $notification): bool
    {
        $current = (bool) ($customer->readNotifications()
            ->where('notifications.id', $notification->id)
            ->first()?->pivot?->is_starred ?? false);

        $this->upsert($customer, $notification, ['is_starred' => ! $current]);

        return ! $current;
    }

    public function toggleArchive(Customer $customer, Notification $notification): bool
    {
        $current = (bool) ($customer->readNotifications()
            ->where('notifications.id', $notification->id)
            ->first()?->pivot?->is_archived ?? false);

        $this->upsert($customer, $notification, ['is_archived' => ! $current]);

        return ! $current;
    }

    /**
     * Create or update the notification_reads row for this customer.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function upsert(Customer $customer, Notification $notification, array $attributes): void
    {
        $existing = DB::table('notification_reads')
            ->where('notification_id', $notification->id)
            ->where('customer_id', $customer->id)
            ->first();

        if ($existing) {
            DB::table('notification_reads')
                ->where('id', $existing->id)
                ->update($attributes + ['updated_at' => now()]);

            return;
        }

        DB::table('notification_reads')->insert($attributes + [
            'notification_id' => $notification->id,
            'customer_id' => $customer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
