<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Teacher;
use App\Models\ParentModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;

class Auth extends Controller
{
    /**
     * Show login form
     */
    public function login()
    {
        // Redirect if already logged in
        if (Session::has('user_type')) {
            $userType = Session::get('user_type');
            if ($userType === 'Admin') {
                return redirect()->route('AdminDashboard');
            } elseif ($userType === 'Teacher') {
                return redirect()->route('teachersDashboard');
            }
        }

        return view('login');
    }

    /**
     * Authenticate user
     */
    public function auth(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:1',
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('username'));
        }

        $username = $request->username;
        $password = $request->password;

        // Rate limiting - prevent brute force attacks
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return redirect()->back()
                ->with('error', "Too many login attempts. Please try again in {$seconds} seconds.")
                ->withInput($request->only('username'));
        }

        // User Authentication
        $userLogin = User::where('name', $username)->first();

        // Check if user exists and password is correct
        if (!$userLogin || !Hash::check($password, $userLogin->password)) {
            RateLimiter::hit($key, 60); // 60 seconds lockout
            return redirect()->back()
                ->with('error', 'Incorrect username or password!')
                ->withInput($request->only('username'));
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

        // Handle different user types
        switch ($userLogin->user_type) {
            case 'Admin':
                $school = School::where('registration_number', $username)->first();

                if (!$school) {
                    return redirect()->back()
                        ->with('error', 'School not found for this admin account.')
                        ->withInput($request->only('username'));
                }

                // Set session data
                Session::put('schoolID', $school->schoolID);
                Session::put('user_type', $userLogin->user_type);
                Session::put('userID', $userLogin->id);
                Session::put('user_name', $userLogin->name);
                Session::put('user_email', $userLogin->email);

                // Regenerate session ID for security
                $request->session()->regenerate();

                return redirect()->route('AdminDashboard')
                    ->with('success', 'You have logged in successfully as Admin!');
                break;

            case 'Teacher':
                $teacher = Teacher::where('employee_number', $username)->first();

                if (!$teacher) {
                    return redirect()->back()
                        ->with('error', 'Teacher record not found.')
                        ->withInput($request->only('username'));
                }

                // Set session data
                Session::put('schoolID', $teacher->schoolID);
                Session::put('teacherID', $teacher->id);
                Session::put('user_type', $userLogin->user_type);
                Session::put('userID', $userLogin->id);
                Session::put('user_name', $userLogin->name);
                Session::put('user_email', $userLogin->email);
                Session::put('teacher_name', $teacher->first_name . ' ' . $teacher->last_name);

                // Load teacher roles if Spatie is installed
                if (class_exists(\Spatie\Permission\Models\Permission::class) && method_exists($teacher, 'roles')) {
                    $roles = $teacher->roles()->pluck('name')->toArray();
                    Session::put('teacher_roles', $roles);
                }

                // Regenerate session ID for security
                $request->session()->regenerate();

                return redirect()->route('teachersDashboard')
                    ->with('success', 'You have logged in successfully as Teacher!');
                break;

                case 'parent':
                $parent = ParentModel::where('phone',$username)->first();
                // Set session data
                Session::put('parentID', $parent->parentID);
                Session::put('schoolID', $parent->schoolID);
                Session::put('user_type', $userLogin->user_type);

                   return redirect()->route('parentDashboard')
                    ->with('success', 'You have logged in successfully as Parent!');
                    break;

            default:
                return redirect()->back()
                    ->with('error', 'Invalid user type.')
                    ->withInput($request->only('username'));
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // Clear all sessions
        Session::flush();

        // Invalidate session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logged out successfully.');
    }
}
