<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use App\Models\Productivity;
use App\Models\WorkAllocation; 
use App\Models\ProductivityGap;
use App\Traits\ResponseBuilderTrait;
use Illuminate\Support\Str;


class ProductivityController extends Controller
{  
   
    use ResponseBuilderTrait;

    public function dashboard(Request $request){
        // Validate input
        $validator = Validator::make($request->all(), [            
            'foremen_code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(),'Validation Failed',400);
        }
        $data = [];

        $data['open_productivities_count'] = WorkAllocation::where('created_by',$request->foremen_code)
                                            ->where('productivity_status', 0)->count();

        $data['submitted_productivities_count'] = WorkAllocation::where('created_by',$request->foremen_code)
                                            ->where('productivity_status', 1)->count();

        return $this->successResponse($data,'Success',200);

    }

    public function getOpenProductivities(Request $request){
        // Validate input
        $validator = Validator::make($request->all(), [            
            'foremen_code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(),'Validation Failed',400);
        }

        $data = WorkAllocation::with([
            'helpers', 
            'scltechnicians', 
            'floors', // Load floors
            'floors.units', // Load units for each floor
            'floors.units.zones', // Load zones for each unit
        ])
        ->where('productivity_status', 0)
        ->where('created_by',$request->foremen_code)->get();

        if ($data->isEmpty()) {
            return $this->errorResponse($data,'No record found',404);            
        }
        else{
            return $this->successResponse($data,'Success',200);
        }
    }
    /**
     * Get all productivity records.
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
        $foremenCode  = $request->foremen_code;

        $data = WorkAllocation::with([
            'helpers', 
            'scltechnicians', 
            'floors', // Load floors
            'floors.units', // Load units for each floor
            'floors.units.zones', // Load zones for each unit
            'productivity.productivityGaps' // Fetch productivity and its gaps

        ])
        ->where('productivity_status', 1)
        ->whereHas('productivity', function ($query) use ($foremenCode, $startDate, $endDate) {
            $query->where('created_by', $foremenCode)
                  ->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->get();

        if ($data->isEmpty()) {
            return $this->errorResponse($data,'No record found',404);            
        }
        else{
            return $this->successResponse($data,'Success',200);
        }
    
    }

    /**
     * Store a new productivity record.
     */
    public function store(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.foremen_code' => 'required|string|max:150',
            'data.*.allocation_code' => 'required|string|max:150',
            'data.*.punch_out_time' => 'required|date_format:H:i:s',
            'data.*.productivity_target' => 'required|integer',
            'data.*.productivity_actual' => 'required|integer',
            'data.*.uom' => 'required|string|max:20',
            'data.*.is_rework' => 'required|boolean',
            'data.*.rework_productivity' => 'integer',
            'data.*.reason_for_gap' => 'array'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(),'Validation Failed',400 );
        }

        try {
            foreach ($request->data as $data) {
                $nextInsertId = Productivity::max('id') + 1;

                $productivityCode = 'P' . Str::random(5). $nextInsertId;                

                $productivity  = Productivity::create([
                    'allocation_code'     => $data['allocation_code'],
                    'productivity_code'   => $productivityCode,
                    'punch_out_time'      => $data['punch_out_time'],
                    'productivity_target' => $data['productivity_target'],
                    'productivity_actual' => $data['productivity_actual'],
                    'uom'                 => $data['uom'],
                    'rework_productivity' => $data['rework_productivity'],
                    'created_by'          => $data['foremen_code'],
                    'updated_by'          => $data['foremen_code'],
                    'is_rework'           => $data['is_rework'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s') 
                ]);

                if($productivity){
                    $allocation = WorkAllocation::where('allocation_code',$data['allocation_code'])->first();
                    $allocation->update([
                        'productivity_status' => 1
                    ]);

                    if(!empty($data['reason_for_gap'])){
                        $reasonData  = []; 
                        foreach ($data['reason_for_gap'] as $reason) { 
                            $reasonData[]  = [
                                'productivity_code'      => $productivityCode,
                                'allocation_code'        => $data['allocation_code'],
                                'reason_for_gap'         => $reason['reason'],
                                'remark'                 => $reason['remark'],
                                'created_at'             => date('Y-m-d H:i:s'),
                                'updated_at'             => date('Y-m-d H:i:s') 
                            ];
                        }
                        ProductivityGap::insert($reasonData);
                    }
                }
            }
            return $this->successResponse(null,'Productivity created successfully',200);

        }
        catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(),'Failed to create productivity',500 );
        }
    }

    /**
     * Get a specific productivity record by ID.
     */
    public function show($id)
    {
        
    }

    /**
     * Update a productivity record.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'foremen_code' => 'required|string|max:150',
            'productivity_code' => 'required|string|max:150',
            'allocation_code' => 'required|string|max:150',
            'punch_out_time' => 'required|date_format:H:i:s',
            'productivity_target' => 'required|integer',
            'productivity_actual' => 'required|integer',
            'uom' => 'required|string|max:20',
            'is_rework' => 'required|boolean',
            'rework_productivity' => 'integer',
            'reason_for_gap' => 'array'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(),'Validation Failed',400 );
        }

        try{
            $productivity = Productivity::where('allocation_code',$request->productivity_code)->first();

            if(!empty($productivity)){
                
                $allocation->update([
                        'punch_out_time'      => $request->punch_out_time,
                        'productivity_target' => $request->productivity_target,
                        'productivity_actual' => $request->productivity_actual,
                        'uom'                 => $request->uom,
                        'rework_productivity' => $request->rework_productivity,
                        'created_by'          => $request->foremen_code,
                        'updated_by'          => $request->foremen_code,
                        'is_rework'           => $request->is_rework,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s') 
                ]);
    
                ProductivityGap::where('productivity_code', $request->productivity_code)->delete();
                if(!empty($request->reason_for_gap)){
                    $reasonData  = []; 
                    foreach ($request->reason_for_gap as $reason) { 
                        $reasonData[]  = [
                            'productivity_code'      => $request->productivity_code,
                            'allocation_code'        => $request->allocation_code,
                            'reason_for_gap'         => $reason['reason'],
                            'remark'                 => $reason['remark'],
                            'created_at'             => date('Y-m-d H:i:s'),
                            'updated_at'             => date('Y-m-d H:i:s') 
                        ];
                    }
                    ProductivityGap::insert($reasonData);
                }
                return $this->successResponse(null,'Productivity updated successfully',200);
            }
            else{
                return $this->errorResponse(Null,'Productivity not found',404 );
            }
        }
        catch (\Exception $e) {

            return $this->errorResponse($e->getMessage(),'Failed to update productivity',500 );

        }
        
    }

    /**
     * Delete a productivity record.
     */
    public function destroy($id)
    {
        
    }
}
