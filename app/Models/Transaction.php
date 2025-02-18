<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'trx_id',
        'proof',
        'phone_number',
        'address',
        'total_amount',
        'product_id',
        'store_id',
        'duration',
        'is_paid',
        'delivery_type',
        'started_at',
        'ended_at',
    ]; 


    public function generateUniqueTrxId(){

        $prefix = 'ZS';
        do{
            $randomString = $prefix . mt_rand(1000, 9999);

        }while (self::where('trx_id', $randomString)->exists());

        return $randomString;
    }

    protected $casts = [
        'total_amount' => MoneyCast::class,
        'started_at' => 'date',
        'ended_at' => 'date'
    ];

    public function store(): BelongsTo{
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo{
        return $this->belongsTo(Product::class);
    }
}
