<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Productivity extends Model
{   
    protected $connection = 'wams'; 
    protected $table = 'productivity';  // Table name
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'allocation_code',
        'productivity_code',
        'punch_out_time',
        'productivity_target',
        'productivity_actual',
        'uom',
        'is_rework',
        'rework_productivity',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function productivityGaps()
    {
        return $this->hasMany(ProductivityGap::class, 'productivity_code', 'productivity_code');
    }


}
