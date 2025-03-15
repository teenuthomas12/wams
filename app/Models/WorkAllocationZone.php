<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkAllocationZone extends Model
{  
    protected $connection = 'wams'; 
    protected $table = 'work_allocation_zones'; 
    protected $primaryKey = 'id';
    public $timestamps = true; // If your table has `created_at` and `updated_at`

    protected $fillable = [
        'allocation_code',
        'flr_code',
        'unit_code',
        'zone_code'
    ];
    
    
}
