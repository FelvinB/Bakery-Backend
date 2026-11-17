<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marketing extends Model
{
    use HasFactory;

    protected $table = 'marketing';
    protected $primaryKey = 'marketing_id';

    protected $fillable = [
        'campaign_name',
        'promotion_type',
        'discount_percentage',
        'campaign_date',
        'sales_generated',
    ];

    protected $casts = [
        'campaign_date' => 'date',
    ];
}
