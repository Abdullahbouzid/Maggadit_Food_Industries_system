<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_en',
        'subject_ar',
        'body_en',
        'body_ar',
        'image',
        'video',
        'group',
        'type',
        'data',
        'channel',
        'icon_type',
        'customers',
        'is_sent',
        'is_read',
        'scheduled_at',
    ];

    protected $casts = [
        'customers' => 'array',
        'data' => 'array',
        'is_sent' => 'boolean',
        'is_read' => 'boolean',
        'scheduled_at' => 'datetime',
    ];

    // Accessor للحصول على رابط الصورة الكامل
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('uploads/'.$this->image) : null;
    }

    // Accessor للحصول على رابط الفيديو
    public function getVideoUrlAttribute()
    {
        return $this->video ? asset('uploads/'.$this->video) : $this->video;
    }

    // علاقة القراءة مع العملاء
    public function readers()
    {
        return $this->belongsToMany(Customer::class, 'notification_reads')
            ->withPivot('read_at', 'is_starred', 'is_archived')
            ->withTimestamps();
    }
}
