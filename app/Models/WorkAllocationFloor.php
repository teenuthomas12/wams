<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkAllocationFloor extends Model
{  
    protected $connection = 'wams'; 
    protected $table = 'work_allocation_floors'; 
    protected $primaryKey = 'id';

    public $timestamps = true; // If your table has `created_at` and `updated_at`

    protected $fillable = [
        'allocation_code',
        'flr_code',
    ];











    
}
