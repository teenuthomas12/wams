<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubContractorSclTechnician extends Model
{  
    protected $connection = 'wams'; 
    protected $table = 'sub_contractor_scl_technicians'; 
    protected $primaryKey = 'id';
    public $timestamps = true; // If your table has `created_at` and `updated_at`

    protected $fillable = [
        'allocation_code',
        'technician_code'
    ];

    
}
