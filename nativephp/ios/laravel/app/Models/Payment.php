<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model
{
    protected $fillable = [
        'payment_number', 'invoice_id', 'customer_id', 'amount',
        'currency_id', 'exchange_rate', 'method', 'reference',
        'received_by', 'paid_at', 'notes',
    ];
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, 'received_by'); }
}