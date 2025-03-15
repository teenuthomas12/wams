<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkTechnicianHelper extends Model
{  
    protected $connection = 'wams'; 
    protected $table = 'work_technician_helpers'; 
    protected $primaryKey = 'id';
    public $timestamps = true; // If your table has `created_at` and `updated_at`

    protected $fillable = [
        'allocation_code',
        'helper_code'
    ];

    
}
