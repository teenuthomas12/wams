<?php

namespace App\Helpers;


use App\Models\WorkAllocationSubcontractor;
use App\Models\WorkAllocationTechnician;
use App\Models\WorkTechnicianHelper;
use App\Models\WorkTechnicianAttendance;
use App\Models\WorkAllocationFloor;
use App\Models\WorkAllocationUnit;
use App\Models\WorkAllocationZone;
use App\Models\SubContractorSclTechnician;

class WorkAllocationHelper
{
    public function storeWorkAllocationSubContractor($allocationCode,$request){

        $workAllocationSubcontractor = WorkAllocationSubcontractor::create([
            'allocation_code'    => $allocationCode,
            'subcontractor_code' => $request['subcontractor_code'],
            'no_of_technicians'  => $request['no_of_technicians'],
            'attendance_date'    => $request['attendance_date'],
            'attendance_time'    => $request['attendance_time'],
            'attendance_status'  => $request['attendance_status'],
            'has_scl_technicians'=> $request['has_scl_technicians'],
            'remarks'            => $request['remarks'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')  
        ]);

        $subcontractorRowCode = $workAllocationSubcontractor->id;

        $data = ['id'=>$subcontractorRowCode];

        return ['message' => 'Success', 'code' => '201','data'=> $data];
    }

    public function storeTechnician($allocationCode, $subcontractorRowCode,$technician){

        $workWithSubcontractor = !empty($subcontractorRowCode) ? 1 :  0;

        $technicianData  = WorkAllocationTechnician::create([
            'allocation_code' => $allocationCode,
            'work_with_subcontractor'=> $workWithSubcontractor,
            'subcontractor_row_code' => $subcontractorRowCode,
            'technician_code'        => $technician['technician_code'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')  
        ]);

        return ['message' => 'Success', 'code' => '201'];

    }

    public function storeHelper($allocationCode, $helpers){

        $helperData  = []; 
        foreach ($helpers as $helperCode) { 
            $helperData[]  = [
                'allocation_code'        => $allocationCode,
                'helper_code'            => $helperCode,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s') 
            ];
        }
        WorkTechnicianHelper::insert($helperData);

        return ['message' => 'Success', 'code' => '201'];

    }

    public function storeTechnicianAttendance($allocationCode, $technician){

        $data  = WorkTechnicianAttendance::create([
            'allocation_code'      => $allocationCode,
            'technician_code'      => $technician['technician_code'],
            'sprinter_date'        => $technician['attendance']['sprinter_date'],
            'sprinter_time'        => $technician['attendance']['sprinter_time'],
            'attendance_date'      => $technician['attendance']['attendance_date'],
            'attendance_time'      => $technician['attendance']['attendance_time'],
            'attendance_status'    => $technician['attendance']['attendance_status'],
            'remarks'              =>$technician['attendance']['remarks'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s') 
        ]);

        return ['message' => 'Success', 'code' => '201'];

    }

    public function storeFloorData($allocationCode, $floor){        

        $floorData  = WorkAllocationFloor::create([
            'allocation_code' => $allocationCode,
            'flr_code'        => $floor['floor_code'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return ['message' => 'Success', 'code' => '201'];

    }

    public function storeUnitsData($allocationCode,$floor,$unit){        

            $unitData  = WorkAllocationUnit::create([
                'allocation_code' => $allocationCode,
                'flr_code'        => $floor['floor_code'],
                'unit_code'       => $unit['unit_code'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        return ['message' => 'Success', 'code' => '201'];
    }

    public function storeZoneData($allocationCode,$floor,$unit){

        $zoneData = [];
        foreach ($unit['zone_code'] as $zone) {
            $zoneData[] = [
                'allocation_code' => $allocationCode,
                'flr_code'        => $floor['floor_code'],
                'unit_code'       => $unit['unit_code'],
                'zone_code' => $zone,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }  
        WorkAllocationZone::insert($zoneData);
        return ['message' => 'Success', 'code' => '201'];
    }

    public function storeSclTechnicians($allocationCode, $sclTechnicians){

        $sclTechData  = []; 
        foreach ($sclTechnicians as $techCode) { 
            $sclTechData[]  = [
                'allocation_code'        => $allocationCode,
                'technician_code'        => $techCode,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s') 
            ];
        }
        SubContractorSclTechnician::insert($sclTechData);

        return ['message' => 'Success', 'code' => '201'];

    }

}

?>