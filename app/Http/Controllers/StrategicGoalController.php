<?php

namespace App\Http\Controllers;

use App\Models\StrategicGoal;
use App\Models\School;
use App\Services\SmsService;
use App\Services\SgpmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class StrategicGoalController extends Controller
{
    protected $smsService;
    protected $notificationService;

    public function __construct()
    {
        $this->smsService = new SmsService();
        $this->notificationService = new SgpmNotificationService();
    }

    public function index()
    {
        $schoolID = Session::get('schoolID');
        $userType = Session::get('user_type');

        if (!$schoolID) {
            return redirect()->route('login');
        }

        // Security: Only Admin and Board can see global strategic goals
        if ($userType !== 'Admin' && $userType !== 'Board') {
            return redirect()->back()->with('error', 'Unauthorized access!');
        }

        $goals = StrategicGoal::where('schoolID', $schoolID)->orderBy('timeline_date', 'asc')->get();
        // Pass user_type for dynamic navigation
        return view('sgpm.goals.index', compact('goals', 'userType'));
    }

    public function store(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $userID = Session::get('userID');

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'kpi' => 'required|string',
            'target_value' => 'required|string',
            'timeline_date' => 'required|date',
            'supporting_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        $goal = new StrategicGoal();
        $goal->schoolID = $schoolID;
        $goal->title = $request->title;
        $goal->description = $request->description;
        $goal->kpi = $request->kpi;
        $goal->target_value = $request->target_value;
        $goal->timeline_date = $request->timeline_date;
        $goal->created_by = $userID;
        $goal->status = 'Draft';

        if ($request->hasFile('supporting_document')) {
            $path = $request->file('supporting_document')->store('sgpm/goals', 'public');
            $goal->supporting_document = $path;
        }

        $goal->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Strategic Goal created in Draft!']);
        }

        return redirect()->back()->with('success', 'Strategic Goal created successfully!');
    }

    public function publish($id)
    {
        $goal = StrategicGoal::findOrFail($id);
        $goal->status = 'Published';
        $goal->save();

        // Notify Admins
        $this->notificationService->notifyAdmin(
            'Strategic Goal Published',
            "Malengo ya Kimkakati (Strategic Goals) yamechapishwa: {$goal->title}. Tafadhali yaangalie.",
            route('sgpm.goals.index'),
            $goal->schoolID
        );

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Goal published and Admin notified!']);
        }

        return redirect()->back()->with('success', 'Strategic Goal published successfully!');
    }

    public function update(Request $request, $id)
    {
        $goal = StrategicGoal::findOrFail($id);
        
        if ($goal->status !== 'Draft') {
            return response()->json(['success' => false, 'message' => 'Cannot edit a published or completed goal.'], 422);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'kpi' => 'required|string',
            'target_value' => 'required|string',
            'timeline_date' => 'required|date',
        ]);

        $goal->title = $request->title;
        $goal->description = $request->description;
        $goal->kpi = $request->kpi;
        $goal->target_value = $request->target_value;
        $goal->timeline_date = $request->timeline_date;

        if ($request->hasFile('supporting_document')) {
            if ($goal->supporting_document) {
                Storage::disk('public')->delete($goal->supporting_document);
            }
            $path = $request->file('supporting_document')->store('sgpm/goals', 'public');
            $goal->supporting_document = $path;
        }

        $goal->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Goal updated successfully!']);
        }

        return redirect()->back()->with('success', 'Strategic Goal updated successfully!');
    }

    public function destroy($id)
    {
        $goal = StrategicGoal::findOrFail($id);
        if ($goal->status === 'Draft') {
            if ($goal->supporting_document) {
                Storage::disk('public')->delete($goal->supporting_document);
            }
            $goal->delete();
            
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Goal deleted!']);
            }
            return redirect()->back()->with('success', 'Goal deleted!');
        }
        
        if (request()->ajax()) {
            return response()->json(['success' => false, 'message' => 'Only draft goals can be deleted.'], 422);
        }
        return redirect()->back()->with('error', 'Only draft goals can be deleted.');
    }
}
