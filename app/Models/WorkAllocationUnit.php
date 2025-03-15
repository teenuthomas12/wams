<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkAllocationUnit extends Model
{  
    protected $connection = 'wams'; 
    protected $table = 'work_allocation_units'; 
    public $timestamps = true; // If your table has `created_at` and `updated_at`
    protected $primaryKey = 'id';

    protected $fillable = [
        'allocation_code',
        'flr_code',
        'unit_code',
    ];
    




    
}
