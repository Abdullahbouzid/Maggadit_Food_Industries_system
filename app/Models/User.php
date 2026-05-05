<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use  HasFactory, Notifiable, HasRoles;
    
    protected $table = 'users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'warehouse_id',
        'is_active',
        'email_verified_at'
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    protected $appends = ['role_names', 'role_colors'];
    
    // العلاقات
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('name')->implode(', ');
    }
    
    public function getRoleColorsAttribute()
    {
        $roles = $this->roles;
        $colors = [];
        foreach ($roles as $role) {
            $colors[] = match($role->name) {
                'super_admin' => 'danger',
                'admin' => 'warning',
                'warehouse_manager' => 'info',
                'sales_manager' => 'success',
                default => 'secondary',
            };
        }
        return $colors;
    }
    
    public function hasPermissionToAccess($permission)
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }
        
        return $this->hasPermissionTo($permission);
    }
    
    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}