<?php

namespace App\Http\Controllers;

use App\Models\GradeDefinition;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GradeDefinitionController extends Controller
{
    /**
     * Display a listing of grade definitions.
     */
    public function index(Request $request)
    {
        $userType = Session::get('user_type');
        
        if (!$userType) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized access'], 401);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = Session::get('schoolID');
        $classID = $request->get('classID');
        
        // Get all classes for this school
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();
        
        // Get grade definitions
        $query = GradeDefinition::with('classModel')
            ->whereHas('classModel', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            });
        
        // Filter by class if provided
        if ($classID) {
            $query->where('classID', $classID);
        }
        
        $gradeDefinitions = $query->orderBy('classID')
            ->orderBy('first', 'desc')
            ->get();
        
        // Group by class for display
        $groupedDefinitions = $gradeDefinitions->groupBy('classID');
        
        // If AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $gradeDefinitions->map(function($def) {
                    return [
                        'gradeDefinitionID' => $def->gradeDefinitionID,
                        'classID' => $def->classID,
                        'class_name' => $def->classModel->class_name ?? 'N/A',
                        'grade' => $def->grade,
                        'first' => $def->first,
                        'last' => $def->last,
                    ];
                })
            ]);
        }
        
        return view('Admin.manage_grade_definitions', compact('classes', 'gradeDefinitions', 'groupedDefinitions'));
    }

    /**
     * Store a newly created grade definition.
     */
    public function store(Request $request)
    {
        // Ensure first and last are numeric
        $first = is_numeric($request->first) ? (float)$request->first : null;
        $last = is_numeric($request->last) ? (float)$request->last : null;
        
        $validator = Validator::make([
            'classID' => $request->classID,
            'first' => $first,
            'last' => $last,
            'grade' => $request->grade,
        ], [
            'classID' => 'required|exists:classes,classID',
            'first' => 'required|numeric|min:0|max:100',
            'last' => 'required|numeric|min:0|max:100',
            'grade' => 'required|string|max:10',
        ], [
            'classID.required' => 'Please select a class.',
            'classID.exists' => 'Selected class does not exist.',
            'first.required' => 'First mark is required.',
            'first.numeric' => 'First mark must be a number.',
            'first.min' => 'First mark must be at least 0.',
            'first.max' => 'First mark cannot exceed 100.',
            'last.required' => 'Last mark is required.',
            'last.numeric' => 'Last mark must be a number.',
            'last.min' => 'Last mark must be at least 0.',
            'last.max' => 'Last mark cannot exceed 100.',
            'grade.required' => 'Grade is required.',
            'grade.max' => 'Grade cannot exceed 10 characters.',
        ]);
        
        // Custom validation: last must be >= first
        if ($first !== null && $last !== null && $last < $first) {
            $validator->errors()->add('last', 'Last mark (' . $last . ') must be greater than or equal to first mark (' . $first . ').');
        }

        // Check custom validation
        if ($first !== null && $last !== null && $last < $first) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'last' => ['Last mark (' . $last . ') must be greater than or equal to first mark (' . $first . ').']
                ]
            ], 422);
        }
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if grade already exists for this class
        $existing = GradeDefinition::where('classID', $request->classID)
            ->where('grade', strtoupper($request->grade))
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This grade already exists for the selected class.',
            ], 422);
        }

        // Check for overlapping ranges
        $overlapping = GradeDefinition::where('classID', $request->classID)
            ->where(function($query) use ($first, $last) {
                $query->whereBetween('first', [$first, $last])
                    ->orWhereBetween('last', [$first, $last])
                    ->orWhere(function($q) use ($first, $last) {
                        $q->where('first', '<=', $first)
                          ->where('last', '>=', $last);
                    });
            })
            ->first();

        if ($overlapping) {
            return response()->json([
                'success' => false,
                'message' => 'This mark range overlaps with an existing grade definition for this class.',
            ], 422);
        }

        try {
            $gradeDefinition = GradeDefinition::create([
                'classID' => $request->classID,
                'first' => $first,
                'last' => $last,
                'grade' => strtoupper($request->grade),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grade definition created successfully.',
                'data' => $gradeDefinition->load('classModel'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create grade definition: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified grade definition.
     */
    public function update(Request $request, $id)
    {
        $gradeDefinition = GradeDefinition::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'classID' => 'required|exists:classes,classID',
            'first' => 'required|numeric|min:0|max:100',
            'last' => 'required|numeric|min:0|max:100|gte:first',
            'grade' => 'required|string|max:10',
        ], [
            'classID.required' => 'Please select a class.',
            'classID.exists' => 'Selected class does not exist.',
            'first.required' => 'First mark is required.',
            'first.numeric' => 'First mark must be a number.',
            'first.min' => 'First mark must be at least 0.',
            'first.max' => 'First mark cannot exceed 100.',
            'last.required' => 'Last mark is required.',
            'last.numeric' => 'Last mark must be a number.',
            'last.min' => 'Last mark must be at least 0.',
            'last.max' => 'Last mark cannot exceed 100.',
            'last.gte' => 'Last mark must be greater than or equal to first mark.',
            'grade.required' => 'Grade is required.',
            'grade.max' => 'Grade cannot exceed 10 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if grade already exists for this class (excluding current record)
        $existing = GradeDefinition::where('classID', $request->classID)
            ->where('grade', $request->grade)
            ->where('gradeDefinitionID', '!=', $id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This grade already exists for the selected class.',
            ], 422);
        }

        // Check for overlapping ranges (excluding current record)
        $overlapping = GradeDefinition::where('classID', $request->classID)
            ->where('gradeDefinitionID', '!=', $id)
            ->where(function($query) use ($request) {
                $query->whereBetween('first', [$request->first, $request->last])
                    ->orWhereBetween('last', [$request->first, $request->last])
                    ->orWhere(function($q) use ($request) {
                        $q->where('first', '<=', $request->first)
                          ->where('last', '>=', $request->last);
                    });
            })
            ->first();

        if ($overlapping) {
            return response()->json([
                'success' => false,
                'message' => 'This mark range overlaps with an existing grade definition for this class.',
            ], 422);
        }

        try {
            $gradeDefinition->update([
                'classID' => $request->classID,
                'first' => $request->first,
                'last' => $request->last,
                'grade' => strtoupper($request->grade),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grade definition updated successfully.',
                'data' => $gradeDefinition->load('classModel'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update grade definition: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified grade definition.
     */
    public function destroy($id)
    {
        try {
            $gradeDefinition = GradeDefinition::findOrFail($id);
            $gradeDefinition->delete();

            return response()->json([
                'success' => true,
                'message' => 'Grade definition deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete grade definition: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get grade definition by ID (for editing).
     */
    public function show($id)
    {
        try {
            $gradeDefinition = GradeDefinition::with('classModel')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $gradeDefinition,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Grade definition not found.',
            ], 404);
        }
    }
}
