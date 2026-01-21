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
