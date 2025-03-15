<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityGap extends Model
{   
    protected $connection = 'wams'; 
    protected $table = 'productivity_gap';  
    protected $primaryKey = 'id';
    public $timestamps = true; 

    protected $fillable = [
        'productivity_code',
        'allocation_code',        
        'reason_for_gap',
        'remark',        
        'created_at',
        'updated_at'
    ];

}

