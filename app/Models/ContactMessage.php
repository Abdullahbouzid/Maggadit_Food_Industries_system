<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'importance',
        'forwarded_to_email',
        'forwarded_at',
    ];

    protected $casts = [
        'forwarded_to_email' => 'boolean',
        'forwarded_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeUnread($query)
    {
        return $query->whereIn('status', ['new', 'read']);
    }

    public function scopeImportant($query)
    {
        return $query->whereIn('importance', ['high', 'urgent']);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByImportance($query, $importance)
    {
        return $query->where('importance', $importance);
    }
}
