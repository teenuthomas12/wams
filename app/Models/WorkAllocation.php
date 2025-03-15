<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkAllocation extends Model
{  
    protected $connection = 'wams'; 
    protected $table = 'work_allocation'; 
    protected $primaryKey = 'id';
    public $timestamps = true; // If your table has `created_at` and `updated_at`

    protected $fillable = [
        'allocation_code',
        'allocation_type',
        'com_code',
        'bu_code',
        'pro_code',
        'bldg_code',
        'div_code',
        'sub_div_code',
        'act_code',
        'sub_act_code',
        'elvn_code',
        'bnd_code',
        'act_uom',
        'is_team',
        'team_count',
        'technician_code',
        'subcontractor_code',
        'no_of_technicians',
        'sprinter_date',
        'sprinter_time',
        'attendance_date',
        'attendance_time',
        'attendance_status',
        'has_scl_technicians',
        'remark',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'productivity_status'
    ];


    public function helpers()
    {
        return $this->hasMany(WorkTechnicianHelper::class, 'allocation_code', 'allocation_code');
    }

    public function scltechnicians()
    {
        return $this->hasMany(SubContractorSclTechnician::class, 'allocation_code', 'allocation_code');
    }

    public function floors()
    {
        return $this->hasMany(WorkAllocationFloor::class, 'allocation_code', 'allocation_code');
    }

    public function productivity()
    {
        return $this->hasOne(Productivity::class, 'allocation_code', 'allocation_code');
    }




    



    

}
