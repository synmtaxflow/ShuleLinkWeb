<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\Combie;
use App\Models\Attendance;
use App\Models\School;
use App\Models\Block;
use App\Models\Room;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\ParentModel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Barryvdh\DomPDF\PDF;

class AccomodationController extends Controller
{
    public function manage_accomodation()
    {
      $userType = Session::get('user_type');
      $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        return view('Admin.manage_accomodation');
    }

    private function ensureAdminAccess()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || $userType !== 'Admin' || !$schoolID) {
            return [false, response()->json(['success' => false, 'message' => 'Unauthorized access'], 403)];
        }

        return [true, $schoolID];
    }

    public function apiBlocksIndex()
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $blocks = Block::where('schoolID', $schoolID)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($block) {
                return [
                    'blockID' => $block->blockID,
                    'blockName' => $block->block_name,
                    'location' => $block->location,
                    'blockType' => $block->block_type,
                    'blockSex' => Schema::hasColumn('blocks', 'block_sex') ? $block->block_sex : null,
                    'description' => $block->description,
                    'status' => $block->status,
                    'items' => (Schema::hasColumn('blocks', 'block_items') && $block->block_items)
                        ? json_decode($block->block_items, true)
                        : [],
                ];
            });

        return response()->json(['success' => true, 'data' => $blocks]);
    }

    public function apiBlocksStore(Request $request)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $validated = $request->validate([
            'blockName' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'blockType' => 'required|in:with_rooms,without_rooms',
            'status' => 'required|in:Active,Inactive',
            'blockSex' => 'nullable|in:Male,Female,Mixed',
            'items' => 'nullable|array',
        ]);

        $blockData = [
            'schoolID' => $schoolID,
            'block_name' => $validated['blockName'],
            'location' => $validated['location'] ?? null,
            'block_type' => $validated['blockType'],
            'description' => $request->input('description'),
            'status' => $validated['status'],
        ];
        if (Schema::hasColumn('blocks', 'block_sex')) {
            $blockData['block_sex'] = $validated['blockSex'] ?? null;
        }
        if (Schema::hasColumn('blocks', 'block_items')) {
            $blockData['block_items'] = !empty($validated['items']) ? json_encode($validated['items']) : null;
        }
        $block = Block::create($blockData);

        return response()->json(['success' => true, 'data' => $block]);
    }

    public function apiBlocksUpdate(Request $request)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $validated = $request->validate([
            'blockID' => 'required|integer',
            'blockName' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'blockType' => 'required|in:with_rooms,without_rooms',
            'status' => 'required|in:Active,Inactive',
            'blockSex' => 'nullable|in:Male,Female,Mixed',
            'items' => 'nullable|array',
        ]);

        $block = Block::where('schoolID', $schoolID)->where('blockID', $validated['blockID'])->firstOrFail();

        if (Schema::hasColumn('blocks', 'block_sex') && !empty($validated['blockSex'])) {
            $blockSex = strtolower($validated['blockSex']);
            if ($blockSex !== 'mixed') {
                $bedIds = Bed::where('blockID', $block->blockID)->pluck('bedID');
                if ($bedIds->isNotEmpty()) {
                    $mismatch = BedAssignment::where('schoolID', $schoolID)
                        ->whereIn('bedID', $bedIds)
                        ->whereNull('released_at')
                        ->join('students', 'bed_assignments.studentID', '=', 'students.studentID')
                        ->where(function ($query) use ($blockSex) {
                            $query->whereNull('students.gender')
                                ->orWhereRaw('LOWER(students.gender) != ?', [$blockSex]);
                        })
                        ->exists();
                    if ($mismatch) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Student sex not allowed in this block. Remove students or change to Mixed.',
                        ], 422);
                    }
                }
            }
        }
        $blockData = [
            'block_name' => $validated['blockName'],
            'location' => $validated['location'] ?? null,
            'block_type' => $validated['blockType'],
            'description' => $request->input('description'),
            'status' => $validated['status'],
        ];
        if (Schema::hasColumn('blocks', 'block_sex')) {
            $blockData['block_sex'] = $validated['blockSex'] ?? null;
        }
        if (Schema::hasColumn('blocks', 'block_items')) {
            $blockData['block_items'] = !empty($validated['items']) ? json_encode($validated['items']) : null;
        }
        $block->update($blockData);

        return response()->json(['success' => true]);
    }

    public function apiBlocksDestroy($blockID)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $block = Block::where('schoolID', $schoolID)->where('blockID', $blockID)->firstOrFail();

        DB::transaction(function () use ($block) {
            Bed::where('blockID', $block->blockID)->delete();
            Room::where('blockID', $block->blockID)->delete();
            $block->delete();
        });

        return response()->json(['success' => true]);
    }

    public function apiRoomsIndex()
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $rooms = Room::where('schoolID', $schoolID)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($room) {
                $items = [];
                $map = [
                    'table' => $room->tables,
                    'chair' => $room->chairs,
                    'cabinet' => $room->cabinets,
                    'wardrobe' => $room->wardrobes,
                ];
                foreach ($map as $type => $qty) {
                    if ($qty && $qty > 0) {
                        $items[] = ['itemType' => $type, 'quantity' => (int)$qty];
                    }
                }
                if ($room->other_items && $room->other_items > 0) {
                    $items[] = ['itemType' => 'other', 'quantity' => (int)$room->other_items];
                }

                return [
                    'roomID' => $room->roomID,
                    'blockID' => $room->blockID,
                    'roomName' => $room->room_name,
                    'roomNumber' => $room->room_number,
                    'capacity' => $room->capacity,
                    'status' => $room->status,
                    'description' => $room->description,
                    'items' => $items,
                ];
            });

        return response()->json(['success' => true, 'data' => $rooms]);
    }

    public function apiRoomsStore(Request $request)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $validated = $request->validate([
            'blockID' => 'required|integer|exists:blocks,blockID',
            'roomName' => 'required|string|max:150',
            'roomNumber' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:Active,Inactive',
            'items' => 'nullable|array',
        ]);

        $room = Room::create([
            'schoolID' => $schoolID,
            'blockID' => $validated['blockID'],
            'room_name' => $validated['roomName'],
            'room_number' => $validated['roomNumber'],
            'capacity' => $validated['capacity'],
            'status' => $validated['status'],
            'description' => $request->input('description'),
            'tables' => $this->itemsCount($validated['items'] ?? [], 'table'),
            'chairs' => $this->itemsCount($validated['items'] ?? [], 'chair'),
            'cabinets' => $this->itemsCount($validated['items'] ?? [], 'cabinet'),
            'wardrobes' => $this->itemsCount($validated['items'] ?? [], 'wardrobe'),
            'other_items' => $this->itemsCount($validated['items'] ?? [], 'other'),
        ]);

        return response()->json(['success' => true, 'data' => $room]);
    }

    public function apiRoomsUpdate(Request $request)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $validated = $request->validate([
            'roomID' => 'required|integer',
            'blockID' => 'required|integer|exists:blocks,blockID',
            'roomName' => 'required|string|max:150',
            'roomNumber' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:Active,Inactive',
            'items' => 'nullable|array',
        ]);

        $room = Room::where('schoolID', $schoolID)->where('roomID', $validated['roomID'])->firstOrFail();
        $room->update([
            'blockID' => $validated['blockID'],
            'room_name' => $validated['roomName'],
            'room_number' => $validated['roomNumber'],
            'capacity' => $validated['capacity'],
            'status' => $validated['status'],
            'description' => $request->input('description'),
            'tables' => $this->itemsCount($validated['items'] ?? [], 'table'),
            'chairs' => $this->itemsCount($validated['items'] ?? [], 'chair'),
            'cabinets' => $this->itemsCount($validated['items'] ?? [], 'cabinet'),
            'wardrobes' => $this->itemsCount($validated['items'] ?? [], 'wardrobe'),
            'other_items' => $this->itemsCount($validated['items'] ?? [], 'other'),
        ]);

        return response()->json(['success' => true]);
    }

    public function apiRoomsDestroy($roomID)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $room = Room::where('schoolID', $schoolID)->where('roomID', $roomID)->firstOrFail();
        DB::transaction(function () use ($room) {
            Bed::where('roomID', $room->roomID)->delete();
            $room->delete();
        });

        return response()->json(['success' => true]);
    }

    public function apiBedsIndex()
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $beds = Bed::where('schoolID', $schoolID)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($bed) {
                return [
                    'bedID' => $bed->bedID,
                    'blockID' => $bed->blockID,
                    'roomID' => $bed->roomID,
                    'bedNumber' => $bed->bed_number,
                    'hasMattress' => $bed->has_mattress,
                    'status' => $bed->status,
                    'description' => $bed->description,
                ];
            });

        return response()->json(['success' => true, 'data' => $beds]);
    }

    public function apiBedAssignmentsIndex()
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $assignments = BedAssignment::with(['bed', 'student.subclass.class', 'student.oldSubclass.class', 'student.parent'])
            ->where('schoolID', $schoolID)
            ->whereNull('released_at')
            ->get()
            ->map(function ($assignment) {
                $student = $assignment->student;
                $className = 'N/A';
                if ($student) {
                    if ($student->subclass && $student->subclass->class) {
                        $className = $student->subclass->class->class_name ?? 'N/A';
                    } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                        $className = $student->oldSubclass->class->class_name ?? 'N/A';
                    }
                }
                $parentPhone = $student && $student->parent ? $student->parent->phone : null;
                $studentName = $student
                    ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''))
                    : 'N/A';

                return [
                    'assignmentID' => $assignment->assignmentID,
                    'bedID' => $assignment->bedID,
                    'studentID' => $assignment->studentID,
                    'studentName' => $studentName,
                    'studentGender' => $student ? $student->gender : null,
                    'className' => $className,
                    'parentPhone' => $parentPhone,
                ];
            });

        return response()->json(['success' => true, 'data' => $assignments]);
    }

    public function apiSearchStudents(Request $request)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $search = trim($request->query('search', ''));
        if ($search === '') {
            return response()->json(['success' => true, 'data' => []]);
        }

        $students = Student::with(['subclass.class', 'oldSubclass.class', 'parent'])
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($student) {
                $className = 'N/A';
                if ($student->subclass && $student->subclass->class) {
                    $className = $student->subclass->class->class_name ?? 'N/A';
                } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                    $className = $student->oldSubclass->class->class_name ?? 'N/A';
                }
                $parentPhone = $student->parent ? $student->parent->phone : null;
                return [
                    'studentID' => $student->studentID,
                    'studentName' => trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'gender' => $student->gender,
                    'className' => $className,
                    'admissionNumber' => $student->admission_number,
                    'parentPhone' => $parentPhone,
                ];
            });

        return response()->json(['success' => true, 'data' => $students]);
    }

    public function apiAssignStudentToBed(Request $request)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $validated = $request->validate([
            'bedID' => 'required|integer|exists:beds,bedID',
            'studentID' => 'required|integer|exists:students,studentID',
        ]);

        $bed = Bed::where('schoolID', $schoolID)->where('bedID', $validated['bedID'])->firstOrFail();
        $student = Student::where('schoolID', $schoolID)->where('studentID', $validated['studentID'])->where('status', 'Active')->firstOrFail();
        $block = Block::where('schoolID', $schoolID)->where('blockID', $bed->blockID)->first();

        if ($bed->status !== 'Available') {
            return response()->json(['success' => false, 'message' => 'Bed is not available for assignment.'], 422);
        }

        $existingBedAssignment = BedAssignment::where('schoolID', $schoolID)
            ->where('bedID', $bed->bedID)
            ->whereNull('released_at')
            ->first();
        if ($existingBedAssignment) {
            return response()->json(['success' => false, 'message' => 'This bed is already occupied.'], 422);
        }

        $existingStudentAssignment = BedAssignment::where('schoolID', $schoolID)
            ->where('studentID', $student->studentID)
            ->whereNull('released_at')
            ->first();
        if ($existingStudentAssignment) {
            return response()->json(['success' => false, 'message' => 'Student already assigned to a bed.'], 422);
        }

        if ($block && Schema::hasColumn('blocks', 'block_sex') && $block->block_sex && strtolower($block->block_sex) !== 'mixed') {
            $blockSex = strtolower($block->block_sex);
            $studentSex = strtolower($student->gender ?? '');
            if ($blockSex === 'male' && $studentSex !== 'male') {
                return response()->json(['success' => false, 'message' => 'Student sex not allowed in this block.'], 422);
            }
            if ($blockSex === 'female' && $studentSex !== 'female') {
                return response()->json(['success' => false, 'message' => 'Student sex not allowed in this block.'], 422);
            }
        }

        DB::transaction(function () use ($schoolID, $bed, $student) {
            BedAssignment::create([
                'schoolID' => $schoolID,
                'bedID' => $bed->bedID,
                'studentID' => $student->studentID,
                'assigned_at' => now(),
            ]);
            $bed->update(['status' => 'Occupied']);
        });

        return response()->json(['success' => true, 'message' => 'Student assigned to bed successfully.']);
    }

    public function apiReleaseStudentFromBed(Request $request)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $validated = $request->validate([
            'bedID' => 'required|integer|exists:beds,bedID',
        ]);

        $assignment = BedAssignment::where('schoolID', $schoolID)
            ->where('bedID', $validated['bedID'])
            ->whereNull('released_at')
            ->first();

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'No active student in this bed.'], 422);
        }

        $bed = Bed::where('schoolID', $schoolID)->where('bedID', $validated['bedID'])->first();

        DB::transaction(function () use ($assignment, $bed) {
            $assignment->update(['released_at' => now()]);
            if ($bed && $bed->status === 'Occupied') {
                $bed->update(['status' => 'Available']);
            }
        });

        return response()->json(['success' => true, 'message' => 'Student removed from bed successfully.']);
    }

    public function apiMoveStudentToBed(Request $request)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $validated = $request->validate([
            'fromBedID' => 'required|integer|exists:beds,bedID',
            'toBedID' => 'required|integer|exists:beds,bedID',
        ]);

        if ($validated['fromBedID'] == $validated['toBedID']) {
            return response()->json(['success' => false, 'message' => 'Select a different target bed.'], 422);
        }

        $fromAssignment = BedAssignment::where('schoolID', $schoolID)
            ->where('bedID', $validated['fromBedID'])
            ->whereNull('released_at')
            ->first();
        if (!$fromAssignment) {
            return response()->json(['success' => false, 'message' => 'Source bed has no student.'], 422);
        }

        $toAssignment = BedAssignment::where('schoolID', $schoolID)
            ->where('bedID', $validated['toBedID'])
            ->whereNull('released_at')
            ->first();

        $fromBed = Bed::where('schoolID', $schoolID)->where('bedID', $validated['fromBedID'])->firstOrFail();
        $toBed = Bed::where('schoolID', $schoolID)->where('bedID', $validated['toBedID'])->firstOrFail();

        $fromStudent = Student::where('schoolID', $schoolID)->where('studentID', $fromAssignment->studentID)->firstOrFail();
        $toStudent = $toAssignment
            ? Student::where('schoolID', $schoolID)->where('studentID', $toAssignment->studentID)->firstOrFail()
            : null;

        $fromBlock = Block::where('schoolID', $schoolID)->where('blockID', $fromBed->blockID)->first();
        $toBlock = Block::where('schoolID', $schoolID)->where('blockID', $toBed->blockID)->first();

        $validateGender = function ($block, $student) {
            if (!$block || !Schema::hasColumn('blocks', 'block_sex') || !$block->block_sex) {
                return true;
            }
            if (strtolower($block->block_sex) === 'mixed') {
                return true;
            }
            $blockSex = strtolower($block->block_sex);
            $studentSex = strtolower($student->gender ?? '');
            if ($blockSex === 'male' && $studentSex !== 'male') {
                return false;
            }
            if ($blockSex === 'female' && $studentSex !== 'female') {
                return false;
            }
            return true;
        };

        if (!$validateGender($toBlock, $fromStudent)) {
            return response()->json(['success' => false, 'message' => 'Student sex not allowed in target block.'], 422);
        }
        if ($toStudent && !$validateGender($fromBlock, $toStudent)) {
            return response()->json(['success' => false, 'message' => 'Target student sex not allowed in source block.'], 422);
        }

        DB::transaction(function () use ($fromAssignment, $toAssignment, $fromBed, $toBed) {
            if ($toAssignment) {
                $toAssignment->update(['bedID' => $fromBed->bedID]);
                $fromAssignment->update(['bedID' => $toBed->bedID]);
                $fromBed->update(['status' => 'Occupied']);
                $toBed->update(['status' => 'Occupied']);
            } else {
                $fromAssignment->update(['bedID' => $toBed->bedID]);
                $toBed->update(['status' => 'Occupied']);
                $fromBed->update(['status' => 'Available']);
            }
        });

        return response()->json(['success' => true, 'message' => 'Student moved successfully.']);
    }

    public function apiRemoveStudentsFromBlock($blockID)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $block = Block::where('schoolID', $schoolID)->where('blockID', $blockID)->firstOrFail();

        $bedIds = Bed::where('blockID', $block->blockID)->pluck('bedID');
        if ($bedIds->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'No beds found in this block.']);
        }

        DB::transaction(function () use ($schoolID, $bedIds) {
            BedAssignment::where('schoolID', $schoolID)
                ->whereIn('bedID', $bedIds)
                ->whereNull('released_at')
                ->update(['released_at' => now()]);
            Bed::whereIn('bedID', $bedIds)->update(['status' => 'Available']);
        });

        return response()->json(['success' => true, 'message' => 'All students removed from the block.']);
    }

    public function apiBedsStore(Request $request)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $validated = $request->validate([
            'blockID' => 'required|integer|exists:blocks,blockID',
            'roomID' => 'nullable|integer|exists:rooms,roomID',
            'bedNumber' => 'nullable|string|max:100',
            'hasMattress' => 'required|in:Yes,No',
            'status' => 'required|in:Available,Occupied,Maintenance,Inactive',
        ]);

        $bed = Bed::create([
            'schoolID' => $schoolID,
            'blockID' => $validated['blockID'],
            'roomID' => $validated['roomID'] ?? null,
            'bed_number' => $validated['bedNumber'] ?? null,
            'has_mattress' => $validated['hasMattress'],
            'status' => $validated['status'],
            'description' => $request->input('description'),
        ]);

        return response()->json(['success' => true, 'data' => $bed]);
    }

    public function apiBedsUpdate(Request $request)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $validated = $request->validate([
            'bedID' => 'required|integer',
            'blockID' => 'required|integer|exists:blocks,blockID',
            'roomID' => 'nullable|integer|exists:rooms,roomID',
            'bedNumber' => 'nullable|string|max:100',
            'hasMattress' => 'required|in:Yes,No',
            'status' => 'required|in:Available,Occupied,Maintenance,Inactive',
        ]);

        $bed = Bed::where('schoolID', $schoolID)->where('bedID', $validated['bedID'])->firstOrFail();
        $bed->update([
            'blockID' => $validated['blockID'],
            'roomID' => $validated['roomID'] ?? null,
            'bed_number' => $validated['bedNumber'] ?? null,
            'has_mattress' => $validated['hasMattress'],
            'status' => $validated['status'],
            'description' => $request->input('description'),
        ]);

        return response()->json(['success' => true]);
    }

    public function apiBedsDestroy($bedID)
    {
        [$ok, $schoolID] = $this->ensureAdminAccess();
        if (!$ok) {
            return $schoolID;
        }

        $bed = Bed::where('schoolID', $schoolID)->where('bedID', $bedID)->firstOrFail();
        $activeAssignment = BedAssignment::where('schoolID', $schoolID)
            ->where('bedID', $bed->bedID)
            ->whereNull('released_at')
            ->first();
        if ($activeAssignment) {
            return response()->json(['success' => false, 'message' => 'Remove student from bed before deleting it.'], 422);
        }
        $bed->delete();

        return response()->json(['success' => true]);
    }

    private function itemsCount(array $items, string $type): int
    {
        foreach ($items as $item) {
            if (($item['itemType'] ?? null) === $type) {
                return (int) ($item['quantity'] ?? 0);
            }
        }
        return 0;
    }
}
