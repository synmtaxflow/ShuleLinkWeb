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
use App\Models\Book;
use App\Models\BookBorrow;
use App\Models\SchoolSubject;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Barryvdh\DomPDF\PDF;

class LibraryController extends Controller
{
    public function manage_library()
    {
      $userType = Session::get('user_type');
      $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get classes for the school
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        // Get subjects for the school
        $subjects = SchoolSubject::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('subject_name')
            ->get();

        // Calculate statistics
        $books = Book::where('schoolID', $schoolID)->get();
        
        $totalBooks = $books->sum('total_quantity');
        $availableBooks = $books->sum('available_quantity');
        $issuedBooks = $books->sum('issued_quantity');
        
        // Count distinct students with borrowed books (status = 'borrowed')
        $borrowedBooks = BookBorrow::whereHas('book', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->where('status', 'borrowed')
            ->distinct()
            ->count('studentID');

        $user_type = $userType;

        return view('Admin.manageLibrary', compact(
            'classes',
            'subjects',
            'totalBooks',
            'availableBooks',
            'issuedBooks',
            'borrowedBooks',
            'user_type'
        ));
    }

    public function get_books(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $classID = $request->input('classID');
            $subjectID = $request->input('subjectID');
            $search = $request->input('search');
            $status = $request->input('status');

            // Build query with eager loading for better performance
            $query = Book::with(['class', 'subject'])
                ->where('schoolID', $schoolID);

            // Filter by class
            if ($classID) {
                $query->where('classID', $classID);
            }

            // Filter by subject
            if ($subjectID) {
                $query->where('subjectID', $subjectID);
            }

            // Filter by status
            if ($status && in_array($status, ['Active', 'Inactive'])) {
                $query->where('status', $status);
            }

            // Search by title, author, or ISBN
            if ($search && trim($search) !== '') {
                $searchTerm = trim($search);
                $query->where(function($q) use ($searchTerm) {
                    $q->where('book_title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('author', 'like', '%' . $searchTerm . '%')
                      ->orWhere('isbn', 'like', '%' . $searchTerm . '%');
                });
            }

            // Get books ordered by title
            $books = $query->orderBy('book_title', 'asc')->get();

            return response()->json([
                'success' => true,
                'books' => $books
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load books: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_book_borrows(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $status = $request->input('status');

            $query = BookBorrow::with([
                'student',
                'book.class',
                'book.subject'
            ])
            ->whereHas('book', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            });

            // Filter by status if provided
            if ($status && in_array($status, ['borrowed', 'returned'])) {
                $query->where('status', $status);
            }

            $borrows = $query->orderBy('borrow_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'borrows' => $borrows
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load borrow records: ' . $e->getMessage()
            ], 500);
        }
    }
}
