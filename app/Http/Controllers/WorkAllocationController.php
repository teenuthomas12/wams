<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\WorkAllocationHelper;
use App\Models\WorkAllocation; 
use App\Traits\ResponseBuilderTrait;
use App\Models\WorkTechnicianAttendance;
use App\Models\WorkAllocationFloor;
use App\Models\WorkAllocationUnit;
use App\Models\WorkAllocationZone;
use App\Models\SubContractorSclTechnician;
use App\Models\WorkTechnicianHelper;
use App\Models\Productivity;

class WorkAllocationController extends Controller
{  

    use ResponseBuilderTrait;


    public $workAllocationHelper;

    public function __construct(WorkAllocationHelper $workAllocationHelper)
    {
        $this->workAllocationHelper = $workAllocationHelper;
    }

    public function dashboard(Request $request){
        // Validate input
        $validator = Validator::make($request->all(), [            
            'foremen_code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(),'Validation Failed',400);
        }
        $data = [];

        $data['allocations_count'] = WorkAllocation::where('created_by',$request->foremen_code)->count();

        return $this->successResponse($data,'Success',200);

    }

    /**
     * Display a listing of job sheets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'foremen_code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(),'Validation Failed',400);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        // Fetch data within the date range

        $allocations = WorkAllocation::with([
            'helpers', 
            'scltechnicians',
            'floors'
        ])
       
        ->whereBetween('work_allocation.created_at', [$startDate, $endDate])
        ->where('work_allocation.created_by', $request->foremen_code)
        ->get();

        $data = [];

        foreach ($allocations as $allocation) {

            $allocationData = [
                'allocation_code' => $allocation->allocation_code,
                'allocation_type' => $allocation->allocation_type,
                'com_code' => $allocation->com_code,
                'bu_code' => $allocation->bu_code,
                'pro_code' => $allocation->pro_code,
                'bldg_code' => $allocation->bldg_code,
                'div_code' => $allocation->div_code,
                'sub_div_code' => $allocation->sub_div_code,
                'act_code' => $allocation->act_code,
                'sub_act_code' => $allocation->sub_act_code,
                'elvn_code' => $allocation->elvn_code ,
                'bnd_code' => $allocation->bnd_code ,
                'act_uom' => $allocation->act_uom ,
                'is_team' => $allocation->is_team ,
                'team_count' => $allocation->team_count ,
                "technician_code"=>$allocation->technician_code,
                "subcontractor_code"=>$allocation->subcontractor_code,
                "no_of_technicians"=>$allocation->no_of_technicians,
                "has_scl_technicians"=>$allocation->has_scl_technicians ,
                "sprinter_date"=>$allocation->sprinter_date,
                "sprinter_time"=>$allocation->sprinter_time,
                "attendance_date"=>$allocation->attendance_date,
                "attendance_time"=>$allocation->attendance_time,
                "attendance_status"=>$allocation->attendance_status,
                'remark' => $allocation->remark ,
                'status' =>  $allocation->status ,
                'created_by' => $allocation->created_by,
                'created_at' => $allocation->created_at,
                'updated_by' => $allocation->updated_by,
                'updated_at' => $allocation->updated_at,
                'helpers' => $allocation->helpers,
                'scltechnicians' => $allocation->scltechnicians,
                'floors' => []
            ];
            
            $allocationData['floors']   = $this->workAllocationHelper->getAllocatedFloorsUnitsZones($allocation->allocation_code,$allocation->floors );

            $data[] = $allocationData;
        }
        if (!$data) {
            return $this->errorResponse($data,'No record found',404);            
        }
        else{
            return $this->successResponse($data,'Success',200);
        }
    }

    /**
     * Store a newly created job sheet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {       
    
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.foremen_code' => 'required|string|max:150',
            'data.*.allocation_type' => 'required|string|in:SCL,Sub Contractor',
            'data.*.com_code' => 'required|string|max:150',
            'data.*.bu_code' => 'required|string|max:150',
            'data.*.pro_code' => 'required|string|max:150',
            'data.*.bldg_code' => 'required|string|max:150',
            'data.*.div_code' => 'required|string|max:150',
            'data.*.sub_div_code' => 'required|string|max:150',
            'data.*.act_code' => 'required|string|max:150',
            'data.*.sub_act_code' => 'required|string|max:150',
            'data.*.elvn_code' => 'nullable|string|max:150',
            'data.*.bnd_code' => 'nullable|string|max:150',
            'data.*.act_uom' => 'nullable|string|max:20',
            'data.*.technician_code' => 'required_if:data.*.allocation_type,SCL|string|max:50',
            'data.*.subcontractor_code' => 'required_if:data.*.allocation_type,Sub Contractor|string|max:50',
            'data.*.no_of_technicians' => 'required_if:data.*.subcontractor_code,!=""|integer',
            'data.*.has_scl_technicians' => 'nullable|in:0,1|integer',
            'data.*.scl_technicians' => 'nullable|required_if:data.*.has_scl_technicians,1|array',
            'data.*.is_team' => 'nullable|in:0,1|integer',
            'data.*.team_count' => 'nullable|integer',
            'data.*.helpers' => 'nullable|required_if:data.*.is_team,1|array',
            'data.*.floors' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(),'Validation Failed',400 );
        }

        try {
            foreach ($request->data as $data) {
                $nextInsertId = WorkAllocation::max('id') + 1;

                $allocationCode = 'WA' . Str::random(5). $nextInsertId; //WA20250311[lastTbaleID++]
                $workAllocation = WorkAllocation::create([
                    'allocation_code' => $allocationCode,
                    'allocation_type' => $data['allocation_type'], // SCL or sub contactor
                    'com_code' => $data['com_code'],
                    'bu_code' => $data['bu_code'],
                    'pro_code' => $data['pro_code'],
                    'bldg_code' => $data['bldg_code'],
                    'div_code' => $data['div_code'],
                    'sub_div_code' => $data['sub_div_code'],
                    'act_code' => $data['act_code'],
                    'sub_act_code' => $data['sub_act_code'],
                    'elvn_code' => $data['elvn_code'] ?? null,
                    'bnd_code' => $data['bnd_code'] ?? null,
                    'act_uom' => $data['act_uom'] ?? null,
                    'is_team' => $data['is_team'] ?? 0,  // 0 for individual, 1 for helpers
                    'team_count' => $data['team_count'] ?? 0,
                    "technician_code"=>$data['technician_code'],
                    "subcontractor_code"=>$data['subcontractor_code'],
                    "no_of_technicians"=>$data['no_of_technicians'],
                    "has_scl_technicians"=>$data['has_scl_technicians'] ?? 0,
                    "sprinter_date"=>$data['sprinter_date'],
                    "sprinter_time"=>$data['sprinter_time'],
                    "attendance_date"=>$data['attendance_date'],
                    "attendance_time"=>$data['attendance_time'],
                    "attendance_status"=>$data['attendance_status'],
                    'remark' => $data['remark'] ?? null,
                    'status' =>  1,
                    'created_by' => $data['foremen_code'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $data['foremen_code'],
                    'updated_at' => date('Y-m-d H:i:s')                   
                ]);

                if ($workAllocation) {
                    // work allocation - technician- helper mapping
                    if($data['is_team']  == 1){ // if team, add helpers    
                        if(!empty($data['helpers'])){
                            $helpers  = $data['helpers'];
                            $helperResponse = $this->workAllocationHelper->storeHelper($allocationCode,$helpers); 
                        }
                    }
                    // Check if floor data exists in request
                    if (!empty($data['floors'])) {

                        foreach ($data['floors'] as $floor) {

                            $floorResponse = $this->workAllocationHelper->storeFloorData($allocationCode,$floor); 

                            if(!empty($floor['units'])){

                                foreach ($floor['units'] as $unit) {
                                    $unitResponse = $this->workAllocationHelper->storeUnitsData($allocationCode,$floor,$unit);

                                    if(!empty($unit['zone_code'])){

                                        $zoneResponse = $this->workAllocationHelper->storeZoneData($allocationCode,$floor,$unit);

                                    }
                                }
                            }
                        }
                    } 
                    
                    if($data['allocation_type'] != "SCL"){
                        if($data['has_scl_technicians'] == 1){ // if suncontractor
                            if(!empty($data['scl_technicians'])){
                                $sclTechnicians = $data['scl_technicians'];
                                $sclTechResponse = $this->workAllocationHelper->storeSclTechnicians($allocationCode,$sclTechnicians);
                            }
                        }
                    }
                    
                }
            }
            return $this->successResponse(null,'Work Allocations created successfully',200);

        } catch (\Exception $e) {

            return $this->errorResponse($e->getMessage(),'Failed to create work allocations',500 );

        }
    }

    /**
     * Display the specified job sheet.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {      


    }

    /**
     * Update the specified job sheet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {  

        $validator = Validator::make($request->all(), [
            'foremen_code' => 'required|string|max:150',
            'allocation_type' => 'required|string|in:SCL,Sub Contractor',
            'com_code' => 'required|string|max:150',
            'bu_code' => 'required|string|max:150',
            'pro_code' => 'required|string|max:150',
            'bldg_code' => 'required|string|max:150',
            'div_code' => 'required|string|max:150',
            'sub_div_code' => 'required|string|max:150',
            'act_code' => 'required|string|max:150',
            'sub_act_code' => 'required|string|max:150',
            'elvn_code' => 'nullable|string|max:150',
            'bnd_code' => 'nullable|string|max:150',
            'act_uom' => 'nullable|string|max:20',
            'technician_code' => 'required_if:allocation_type,SCL|string|max:50',
            'subcontractor_code' => 'required_if:allocation_type,Sub Contractor|string|max:50',
            'no_of_technicians' => 'required_if:subcontractor_code,!=""|integer',
            'has_scl_technicians' => 'nullable|in:0,1|integer',
            'scl_technicians' => 'nullable|required_if:has_scl_technicians,1|array',
            'is_team' => 'nullable|in:0,1|integer',
            'team_count' => 'nullable|integer',
            'helpers' => 'nullable|required_if:is_team,1|array',
            'floors' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(),'Validation Failed',400 );
        }

        try{
            $allocationCode  = $id;
            $allocation = WorkAllocation::where('allocation_code',$allocationCode)->first();

            if(!empty($allocation)){
    
                $allocation->update([
                    'allocation_type' => $request->allocation_type ,
                    'com_code' => $request->com_code ,
                    'bu_code' => $request->bu_code ,
                    'pro_code' => $request->pro_code ,
                    'bldg_code' => $request->bldg_code ,
                    'div_code' => $request->div_code ,
                    'sub_div_code' => $request->sub_div_code ,
                    'act_code' => $request->act_code ,
                    'sub_act_code' => $request->sub_act_code ,
                    'elvn_code' => $request->elvn_code ,
                    'bnd_code' => $request->bnd_code ,
                    'act_uom' => $request->act_uom,
                    'is_team' => $request->is_team ,
                    'team_count' => $request->team_count ,
                    'technician_code' => $request->technician_code,
                    'subcontractor_code' => $request->subcontractor_code ,
                    'no_of_technicians' => $request->no_of_technicians ,
                    'has_scl_technicians' => $request->has_scl_technicians ,
                    'sprinter_date' => $request->sprinter_date ,
                    'sprinter_time' => $request->sprinter_time ,
                    'attendance_date' => $request->attendance_date ,
                    'attendance_time' => $request->attendance_time ,
                    'attendance_status' => $request->attendance_status ,
                    'remark' => $request->remark ,
                    'status' => 1,
                    'updated_by' => $request->foremen_code,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
    
                // work allocation - technician- helper mapping
                WorkTechnicianHelper::where('allocation_code', $allocationCode)->delete();
    
                if($request->is_team  == 1){ // if team, add helpers    
                    if(!empty($request->helpers)){
                        $helpers  = $request->helpers;
                        $helperResponse = $this->workAllocationHelper->storeHelper($allocationCode,$helpers); 
                    }
                }
    
                // Update Floors and Units
                WorkAllocationZone::where('allocation_code', $allocationCode)->delete();
                WorkAllocationUnit::where('allocation_code', $allocationCode)->delete();
                WorkAllocationFloor::where('allocation_code', $allocationCode)->delete();
                if (!empty($request->floors)) {
    
                    foreach ($request->floors as $floor) {
    
                        $floorResponse = $this->workAllocationHelper->storeFloorData($allocationCode,$floor); 
    
                        if(!empty($floor['units'])){
    
                            foreach ($floor['units'] as $unit) {
                                $unitResponse = $this->workAllocationHelper->storeUnitsData($allocationCode,$floor,$unit);
    
                                if(!empty($unit['zone_code'])){
    
                                    $zoneResponse = $this->workAllocationHelper->storeZoneData($allocationCode,$floor,$unit);
    
                                }
                            }
                        }
                    }
                } 
                SubContractorSclTechnician::where('allocation_code', $allocationCode)->delete();
                if($request->allocation_type != "SCL"){
                    if($request->has_scl_technicians == 1){ // if suncontractor
                        if(!empty($request->scl_technicians)){
                            $sclTechnicians = $request->scl_technicians;
                            $sclTechResponse = $this->workAllocationHelper->storeSclTechnicians($allocationCode,$sclTechnicians);
                        }
                    }
                }
    
                return $this->successResponse(null,'Work Allocations updated successfully',200);
    
            }
            else{
                return $this->errorResponse(Null,'Work allocation not found',404 );
            }
        }
        catch (\Exception $e) {

            return $this->errorResponse($e->getMessage(),'Failed to update work allocations',500 );

        }
        
    }

    /**
     * Remove the specified job sheet from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($id){
            $allocation = WorkAllocation::where('allocation_code',$id)->first();
            if($allocation){
                SubContractorSclTechnician::where('allocation_code', $id)->delete();
                WorkAllocationZone::where('allocation_code', $id)->delete();
                WorkAllocationUnit::where('allocation_code', $id)->delete();
                WorkAllocationFloor::where('allocation_code', $id)->delete();
                WorkTechnicianHelper::where('allocation_code', $id)->delete();
                WorkAllocation::where('allocation_code',$id)->delete();
                Productivity::where('allocation_code',$id)->delete();
                return $this->successResponse(null,'Work Allocation has been deleted successfully',200);
            }
            else{
                return $this->errorResponse(Null,'Work allocation not found',404 );
            }

        }
        else{
            return $this->errorResponse(Null,'Please enter valid allocation code',404 );
        }
    }
}
