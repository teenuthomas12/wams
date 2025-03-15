<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkAllocationUnit extends Model
{  
    protected $connection = 'wams'; 
    protected $table = 'work_allocation_units'; 
    protected $primaryKey = 'id';
    public $timestamps = true; // If your table has `created_at` and `updated_at`

    protected $fillable = [
        'allocation_code',
        'flr_code',
        'unit_code',
    ];
    public function zones()
    {
        return $this->hasMany(WorkAllocationZone::class, 'allocation_code', 'allocation_code');
    }


    
}
