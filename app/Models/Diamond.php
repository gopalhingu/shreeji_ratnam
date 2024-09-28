<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diamond extends Model
{
    use HasFactory;

    protected $table = 'diamonds';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'stock_id',
        'growth_type',
        'status',
        'reference',
        'range',
        'shape',
        'weight',
        'color',
        'clarity',
        'cut',
        'polish',
        'symmetry',
        'fluorescence_intensity',
        'length',
        'width',
        'height',
        'ratio',
        'lab',
        'report_date',
        'report_number',
        'location',
        'discounts',
        'live_rap',
        'rap_amount',
        'price_per_carat',
        'total_price',
        'bargaining_price_per_carat',
        'bargaining_total_price',
        'depth_percentage',
        'table_percentage',
        'crown_height',
        'crown_angle',
        'pavilion_depth',
        'pavilion_angle',
        'inscription',
        'key_to_symbols',
        'white_inclusion',
        'black_inclusion',
        'open_inclusion',
        'fancy_color',
        'fancy_color_intensity',
        'fancy_color_overtone',
        'girdle_percentage',
        'girdle',
        'culet',
        'state',
        'city',
        'cert_url',
        'video_url',
        'image_url',
        'treatment',
        'country',
        'cert_comment'
    ];
}
