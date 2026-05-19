<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'employee_name', 'employee_code', 'organization_id', 'employee_type',
        'phone', 'mobile', 'email', 'national_id', 'on_hold', 'inactive', 'out_source',
        'organization_access', 'manager_level', 'manager_id', 'divisions', 'default_division',
        'device', 'vehicle', 'territory', 'driver', 'helper', 'region', 'channel', 'items_list',
        'employee_branch_id', 'contact_country_id', 'user_name', 'password', 'currency',
        'balance', 'credit_limit',
        // document sequence fields (all)
        'invoice_id', 'transaction_credit_note', 'transaction_debit_note', 'payment_id',
        'warehouse_transaction_id', 'return_id', 'order_id', 'sample_id', 'applied_payment_id',
        'replenish_id', 'remove_id', 'new_customer_code', 'settlement_id', 'order_invoice_id',
        'return_order_id', 'contracted_foc_id', 'unlimited_foc_id', 'fixed_incentive_id',
        'variable_incentive_id', 'organization_credit_note', 'organization_debit_note',
        'purchase_order_id', 'prison_foc_id', 'guarantees_foc_id', 'gift_voucher_id',
        'icash_transaction_id', 'icash_reversal_id', 'delivery_note_id', 'promotion_transaction_id',
        'max_customer_code', 'credit_note_request', 'customer_app_order_id', 'applied_e_payment_id',
        'customer_app_credit_note_id', 'customer_app_down_payment_id'
    ];

    protected $casts = [
        'on_hold' => 'boolean',
        'inactive' => 'boolean',
        'out_source' => 'boolean',
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'divisions' => 'array',
        'items_list' => 'array',
        'organization_access' => 'array',
    ];

    protected $hidden = ['password'];

    // علاقات
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'employee_branch_id');
    }

    public function contactCountry()
    {
        return $this->belongsTo(Country::class, 'contact_country_id');
    }

    public function securityGroups()
    {
        return $this->belongsToMany(SecurityGroup::class, 'employee_security_group');
    }

    // Mutator لتشفير كلمة المرور
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }
}