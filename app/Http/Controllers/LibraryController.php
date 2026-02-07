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
use App\Models\BookLoss;
use App\Models\BookDamage;
use App\Models\SchoolSubject;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Barryvdh\DomPDF\PDF;
use App\Services\SmsService;

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

        // Calculate statistics using SQL counts
        $totalBooks = Book::where('schoolID', $schoolID)->count();
        $issuedBooks = BookBorrow::whereHas('book', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->where('status', 'borrowed')
            ->distinct('bookID')
            ->count('bookID');
        $availableBooks = max(0, $totalBooks - $issuedBooks);
        $overdueBooks = BookBorrow::whereHas('book', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->where('status', 'borrowed')
            ->whereNotNull('expected_return_date')
            ->where('expected_return_date', '<', Carbon::now()->toDateString())
            ->distinct('bookID')
            ->count('bookID');
        $lostBooks = BookLoss::whereHas('book', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->where('status', 'lost')
            ->distinct('bookID')
            ->count('bookID');
        $damagedBooks = BookDamage::whereHas('book', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->where('status', 'damaged')
            ->distinct('bookID')
            ->count('bookID');
        
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
            'overdueBooks',
            'lostBooks',
            'damagedBooks',
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
            $query = Book::with([
                    'class',
                    'subject',
                    'activeBorrows.student.subclass.class',
                    'activeLosses.student.subclass.class',
                    'activeDamages.student.subclass.class'
                ])
                ->withCount([
                    'activeBorrows as active_borrows_count',
                    'activeLosses as active_losses_count',
                    'activeDamages as active_damages_count'
                ])
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
            $books = $query->orderBy('book_title', 'asc')->get()->map(function($book) {
                $isLost = ($book->active_losses_count ?? 0) > 0;
                $isDamaged = ($book->active_damages_count ?? 0) > 0;
                $isAvailable = ($book->active_borrows_count ?? 0) === 0 && !$isLost && !$isDamaged;
                $activeBorrow = $book->activeBorrows->first();
                $student = $activeBorrow ? $activeBorrow->student : null;
                $className = $student && $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : '-';
                $studentName = $student
                    ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''))
                    : '';
                $admission = $student ? ($student->admission_number ?? '-') : '-';

                $loss = $book->activeLosses->first();
                $damage = $book->activeDamages->first();
                $lossStudent = $loss && $loss->student ? $loss->student : null;
                $damageStudent = $damage && $damage->student ? $damage->student : null;
                $lossClassName = $lossStudent && $lossStudent->subclass && $lossStudent->subclass->class
                    ? $lossStudent->subclass->class->class_name
                    : '-';
                $damageClassName = $damageStudent && $damageStudent->subclass && $damageStudent->subclass->class
                    ? $damageStudent->subclass->class->class_name
                    : '-';

                $book->is_available = $isAvailable;
                $book->is_lost = $isLost;
                $book->is_damaged = $isDamaged;
                $book->total_quantity = 1;
                $book->available_quantity = $isAvailable ? 1 : 0;
                $book->issued_quantity = $isAvailable ? 0 : 1;
                $book->borrow_expected_return_date = $activeBorrow ? $activeBorrow->expected_return_date : null;
                $book->is_overdue = $activeBorrow && $activeBorrow->expected_return_date
                    ? (Carbon::parse($activeBorrow->expected_return_date)->lt(Carbon::today()))
                    : false;
                $book->occupied_by = $student
                    ? [
                        'name' => $studentName,
                        'admission_number' => $admission,
                        'class_name' => $className
                    ]
                    : null;
                $book->loss_info = $loss ? [
                    'lossID' => $loss->lossID,
                    'lost_by' => $loss->lost_by,
                    'description' => $loss->description,
                    'payment_status' => $loss->payment_status,
                    'payment_method' => $loss->payment_method,
                    'payment_amount' => $loss->payment_amount,
                    'student' => $lossStudent ? [
                        'name' => trim(($lossStudent->first_name ?? '') . ' ' . ($lossStudent->middle_name ?? '') . ' ' . ($lossStudent->last_name ?? '')),
                        'admission_number' => $lossStudent->admission_number ?? '-',
                        'class_name' => $lossClassName
                    ] : null
                ] : null;
                $book->damage_info = $damage ? [
                    'damageID' => $damage->damageID,
                    'damaged_by' => $damage->damaged_by,
                    'description' => $damage->description,
                    'payment_status' => $damage->payment_status,
                    'payment_method' => $damage->payment_method,
                    'payment_amount' => $damage->payment_amount,
                    'student' => $damageStudent ? [
                        'name' => trim(($damageStudent->first_name ?? '') . ' ' . ($damageStudent->middle_name ?? '') . ' ' . ($damageStudent->last_name ?? '')),
                        'admission_number' => $damageStudent->admission_number ?? '-',
                        'class_name' => $damageClassName
                    ] : null
                ] : null;

                unset($book->active_borrows_count);
                unset($book->active_losses_count);
                unset($book->active_damages_count);
                return $book;
            });

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

    public function check_isbn(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $isbn = trim($request->input('isbn', ''));
            $bookID = $request->input('bookID');

            if ($isbn === '') {
                return response()->json([
                    'success' => true,
                    'available' => false
                ]);
            }

            $query = Book::where('schoolID', $schoolID)
                ->where('isbn', $isbn);

            if ($bookID) {
                $query->where('bookID', '!=', $bookID);
            }

            $exists = $query->exists();

            return response()->json([
                'success' => true,
                'available' => !$exists
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check ISBN: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_book_by_isbn(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $isbn = trim($request->input('isbn', ''));
            if ($isbn === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'ISBN is required.'
                ], 422);
            }

            $book = Book::with(['class', 'subject', 'activeBorrows.student.subclass.class'])
                ->where('schoolID', $schoolID)
                ->where('isbn', $isbn)
                ->first();

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found.'
                ], 404);
            }

            $activeBorrow = $book->activeBorrows->first();
            $isBorrowed = $activeBorrow ? true : false;
            $isLost = BookLoss::where('bookID', $book->bookID)
                ->where('status', 'lost')
                ->exists();
            $isDamaged = BookDamage::where('bookID', $book->bookID)
                ->where('status', 'damaged')
                ->exists();
            $isAvailable = !$isBorrowed && !$isLost && !$isDamaged && $book->status === 'Active';

            $borrower = null;
            if ($activeBorrow && $activeBorrow->student) {
                $student = $activeBorrow->student;
                $className = $student->subclass && $student->subclass->class ? $student->subclass->class->class_name : '-';
                $borrower = [
                    'studentID' => $student->studentID,
                    'name' => trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'admission_number' => $student->admission_number ?? '-',
                    'class_name' => $className
                ];
            }

            return response()->json([
                'success' => true,
                'book' => $book,
                'is_available' => $isAvailable,
                'borrower' => $borrower
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load book: ' . $e->getMessage()
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
            $dueFilter = $request->input('dueFilter');

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

            if ($dueFilter === 'overdue') {
                $query->where('status', 'borrowed')
                    ->whereNotNull('expected_return_date')
                    ->where('expected_return_date', '<', Carbon::now()->toDateString());
            }
            if ($dueFilter === 'not_due') {
                $query->where('status', 'borrowed')
                    ->whereNotNull('expected_return_date')
                    ->where('expected_return_date', '>=', Carbon::now()->toDateString());
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

    public function get_subjects_by_class(Request $request)
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

            if (!$classID) {
                return response()->json([
                    'success' => false,
                    'error' => 'Class ID is required.'
                ], 400);
            }

            // First verify the class belongs to this school
            $class = ClassModel::where('classID', $classID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$class) {
                return response()->json([
                    'success' => false,
                    'error' => 'Class not found or does not belong to this school.'
                ], 404);
            }

            // Get subjects for this class from class_subjects table
            // Subjects can be assigned to the whole class (classID only) or to specific subclasses
            $subjects = ClassSubject::where('classID', $classID)
                ->where('status', 'Active')
                ->whereNull('subclassID') // Get subjects assigned to whole class, not specific subclasses
                ->with(['subject' => function($query) use ($schoolID) {
                    $query->where('schoolID', $schoolID)
                          ->where('status', 'Active');
                }])
                ->get()
                ->filter(function($classSubject) {
                    return $classSubject->subject !== null;
                })
                ->map(function($classSubject) {
                    return [
                        'subjectID' => $classSubject->subject->subjectID,
                        'subject_name' => $classSubject->subject->subject_name
                    ];
                })
                ->values(); // Re-index array

            // If no subjects found for whole class, try to get unique subjects from subclasses
            if ($subjects->isEmpty()) {
                $subjects = ClassSubject::where('classID', $classID)
                    ->where('status', 'Active')
                    ->with(['subject' => function($query) use ($schoolID) {
                        $query->where('schoolID', $schoolID)
                              ->where('status', 'Active');
                    }])
                    ->get()
                    ->filter(function($classSubject) {
                        return $classSubject->subject !== null;
                    })
                    ->map(function($classSubject) {
                        return [
                            'subjectID' => $classSubject->subject->subjectID,
                            'subject_name' => $classSubject->subject->subject_name
                        ];
                    })
                    ->unique('subjectID') // Get unique subjects
                    ->values();
            }

            return response()->json([
                'success' => true,
                'subjects' => $subjects
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting subjects by class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load subjects: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store_book(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            if ($request->has('isbns')) {
                $validator = Validator::make($request->all(), [
                    'classID' => 'required|exists:classes,classID',
                    'subjectID' => 'required|exists:school_subjects,subjectID',
                    'book_title' => 'required|string|max:255',
                    'author' => 'nullable|string|max:255',
                    'publisher' => 'nullable|string|max:255',
                    'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                    'description' => 'nullable|string',
                    'isbns' => 'required|array|min:1',
                    'isbns.*' => 'required|string|max:50|distinct'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $class = ClassModel::where('classID', $request->classID)
                    ->where('schoolID', $schoolID)
                    ->first();
                if (!$class) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Class not found or does not belong to this school.'
                    ], 404);
                }

                $subject = SchoolSubject::where('subjectID', $request->subjectID)
                    ->where('schoolID', $schoolID)
                    ->first();
                if (!$subject) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Subject not found or does not belong to this school.'
                    ], 404);
                }

                $isbnList = collect($request->isbns)->filter()->values()->all();
                $existingIsbns = Book::where('schoolID', $schoolID)
                    ->whereIn('isbn', $isbnList)
                    ->pluck('isbn')
                    ->toArray();

                if (!empty($existingIsbns)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ISBN already exists: ' . implode(', ', $existingIsbns)
                    ], 422);
                }

                DB::beginTransaction();
                foreach ($isbnList as $isbn) {
                    Book::create([
                        'schoolID' => $schoolID,
                        'classID' => $request->classID,
                        'subjectID' => $request->subjectID,
                        'book_title' => $request->book_title,
                        'author' => $request->author ?? null,
                        'isbn' => $isbn,
                        'publisher' => $request->publisher ?? null,
                        'publication_year' => $request->publication_year ?? null,
                        'total_quantity' => 1,
                        'available_quantity' => 1,
                        'issued_quantity' => 0,
                        'description' => $request->description ?? null,
                        'status' => 'Active'
                    ]);
                }
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Books added successfully',
                    'count' => count($isbnList)
                ]);
            }

            // Validate input for single book add
            $validator = Validator::make($request->all(), [
                'classID' => 'required|exists:classes,classID',
                'subjectID' => 'required|exists:school_subjects,subjectID',
                'book_title' => 'required|string|max:255',
                'author' => 'nullable|string|max:255',
                'isbn' => 'required|string|max:50|unique:books,isbn,NULL,bookID,schoolID,' . $schoolID,
                'publisher' => 'nullable|string|max:255',
                'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify class and subject belong to this school
            $class = ClassModel::where('classID', $request->classID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class not found or does not belong to this school.'
                ], 404);
            }

            $subject = SchoolSubject::where('subjectID', $request->subjectID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found or does not belong to this school.'
                ], 404);
            }

            // Create book
            $book = Book::create([
                'schoolID' => $schoolID,
                'classID' => $request->classID,
                'subjectID' => $request->subjectID,
                'book_title' => $request->book_title,
                'author' => $request->author ?? null,
                'isbn' => $request->isbn,
                'publisher' => $request->publisher ?? null,
                'publication_year' => $request->publication_year ?? null,
                'total_quantity' => 1,
                'available_quantity' => 1,
                'issued_quantity' => 0,
                'description' => $request->description ?? null,
                'status' => 'Active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Book added successfully',
                'book' => $book
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error storing book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save book: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_book(Request $request, $bookID)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            // Find book
            $book = Book::where('bookID', $bookID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found or does not belong to this school.'
                ], 404);
            }

            if ($book->status !== 'Active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Book is inactive and cannot be borrowed.'
                ], 400);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'classID' => 'required|exists:classes,classID',
                'subjectID' => 'required|exists:school_subjects,subjectID',
                'book_title' => 'required|string|max:255',
                'author' => 'nullable|string|max:255',
                'isbn' => 'required|string|max:50|unique:books,isbn,' . $bookID . ',bookID,schoolID,' . $schoolID,
                'publisher' => 'nullable|string|max:255',
                'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'description' => 'nullable|string',
                'status' => 'nullable|in:Active,Inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify class and subject belong to this school
            $class = ClassModel::where('classID', $request->classID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class not found or does not belong to this school.'
                ], 404);
            }

            $subject = SchoolSubject::where('subjectID', $request->subjectID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found or does not belong to this school.'
                ], 404);
            }

            $hasActiveBorrow = BookBorrow::where('bookID', $book->bookID)
                ->where('status', 'borrowed')
                ->exists();
            $availableQuantity = $hasActiveBorrow ? 0 : 1;
            $issuedQuantity = $hasActiveBorrow ? 1 : 0;

            // Update book
            $book->update([
                'classID' => $request->classID,
                'subjectID' => $request->subjectID,
                'book_title' => $request->book_title,
                'author' => $request->author ?? null,
                'isbn' => $request->isbn,
                'publisher' => $request->publisher ?? null,
                'publication_year' => $request->publication_year ?? null,
                'total_quantity' => 1,
                'available_quantity' => $availableQuantity,
                'issued_quantity' => $issuedQuantity,
                'description' => $request->description ?? null,
                'status' => $request->status ?? $book->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Book updated successfully',
                'book' => $book
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update book: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_book($bookID)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            // Find book
            $book = Book::where('bookID', $bookID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found or does not belong to this school.'
                ], 404);
            }

            // Check if book has active borrows
            $activeBorrows = BookBorrow::where('bookID', $bookID)
                ->where('status', 'borrowed')
                ->count();

            if ($activeBorrows > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete book. There are ' . $activeBorrows . ' active borrow(s).'
                ], 400);
            }

            // Delete book
            $book->delete();

            return response()->json([
                'success' => true,
                'message' => 'Book deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete book: ' . $e->getMessage()
            ], 500);
        }
    }

    public function borrow_book(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'isbn' => 'required|string|max:50',
                'studentID' => 'required|exists:students,studentID',
                'expected_return_date' => 'required|date|after_or_equal:today',
                'notes' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                $errorMessages = $validator->errors()->all();
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errorMessages),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find book and verify it belongs to this school
            $book = Book::where('isbn', $request->isbn)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found or does not belong to this school.'
                ], 404);
            }

            // Check if book is lost or damaged
            $hasLoss = BookLoss::where('bookID', $book->bookID)
                ->where('status', 'lost')
                ->exists();
            if ($hasLoss) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book is marked as lost.'
                ], 400);
            }

            $hasDamage = BookDamage::where('bookID', $book->bookID)
                ->where('status', 'damaged')
                ->exists();
            if ($hasDamage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book is marked as damaged.'
                ], 400);
            }

            // Check if book is available
            $activeBorrow = BookBorrow::where('bookID', $book->bookID)
                ->where('status', 'borrowed')
                ->first();

            if ($activeBorrow) {
                $student = $activeBorrow->student;
                $studentName = $student
                    ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''))
                    : 'another student';
                return response()->json([
                    'success' => false,
                    'message' => 'Book is already taken by ' . $studentName . '.'
                ], 400);
            }

            // Verify student belongs to this school
            $student = Student::where('studentID', $request->studentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found, inactive, or does not belong to this school.'
                ], 404);
            }

            // Check if student already has this book borrowed
            $existingBorrow = BookBorrow::where('bookID', $book->bookID)
                ->where('studentID', $request->studentID)
                ->where('status', 'borrowed')
                ->first();

            if ($existingBorrow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student already has this book borrowed.'
                ], 400);
            }

            DB::beginTransaction();

            // Create borrow record
            $borrow = BookBorrow::create([
                'bookID' => $book->bookID,
                'studentID' => $request->studentID,
                'borrow_date' => Carbon::now()->toDateString(),
                'expected_return_date' => $request->expected_return_date ? Carbon::parse($request->expected_return_date)->toDateString() : null,
                'status' => 'borrowed',
                'notes' => $request->notes ?? null
            ]);

            // Update book quantities
            $book->available_quantity = 0;
            $book->issued_quantity = 1;
            $book->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book borrowed successfully',
                'borrow' => $borrow
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error borrowing book: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to borrow book: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function return_book(Request $request, $borrowID)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            // Find borrow record
            $borrow = BookBorrow::with('book')
                ->where('borrowID', $borrowID)
                ->whereHas('book', function($q) use ($schoolID) {
                    $q->where('schoolID', $schoolID);
                })
                ->first();

            if (!$borrow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Borrow record not found or does not belong to this school.'
                ], 404);
            }

            // Check if already returned
            if ($borrow->status === 'returned') {
                return response()->json([
                    'success' => false,
                    'message' => 'Book has already been returned.'
                ], 400);
            }

            DB::beginTransaction();

            $returnDate = $request->input('return_date') ? Carbon::parse($request->input('return_date'))->toDateString() : Carbon::now()->toDateString();
            $lateReason = $request->input('late_reason');

            // Update borrow record
            $borrow->status = 'returned';
            $borrow->return_date = $returnDate;
            if ($lateReason) {
                $borrow->notes = $borrow->notes ? ($borrow->notes . "\nLate reason: " . $lateReason) : ("Late reason: " . $lateReason);
            }
            $borrow->save();

            // Update book quantities
            $book = $borrow->book;
            $book->available_quantity = 1;
            $book->issued_quantity = 0;
            $book->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book returned successfully',
                'borrow' => $borrow
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error returning book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to return book: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_students(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $search = $request->input('search', '');

            if (empty($search) || strlen(trim($search)) < 2) {
                return response()->json([
                    'success' => true,
                    'students' => []
                ]);
            }

            $searchTerm = trim($search);

            // Search students by name or admission number
            $students = Student::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->where(function($query) use ($searchTerm) {
                    $query->where('first_name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('middle_name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('admission_number', 'like', '%' . $searchTerm . '%')
                          ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)"), 'like', '%' . $searchTerm . '%');
                })
                ->with(['subclass.class'])
                ->limit(20) // Limit results
                ->get()
                ->map(function($student) {
                    return [
                        'studentID' => $student->studentID,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'last_name' => $student->last_name,
                        'admission_number' => $student->admission_number,
                        'subclass' => $student->subclass ? [
                            'subclassID' => $student->subclass->subclassID,
                            'subclass_name' => $student->subclass->subclass_name,
                            'class' => $student->subclass->class ? [
                                'classID' => $student->subclass->class->classID,
                                'class_name' => $student->subclass->class->class_name
                            ] : null
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'students' => $students
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load students: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_book_statistics(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $classID = $request->input('classID');
            $subjectID = $request->input('subjectID');

            $stats = DB::table('books')
                ->leftJoin('book_borrows as bb', function($join) {
                    $join->on('bb.bookID', '=', 'books.bookID')
                        ->where('bb.status', '=', 'borrowed');
                })
                ->leftJoin('classes', 'classes.classID', '=', 'books.classID')
                ->leftJoin('school_subjects', 'school_subjects.subjectID', '=', 'books.subjectID')
                ->where('books.schoolID', $schoolID)
                ->when($classID, function($q) use ($classID) {
                    $q->where('books.classID', $classID);
                })
                ->when($subjectID, function($q) use ($subjectID) {
                    $q->where('books.subjectID', $subjectID);
                })
                ->groupBy(
                    'books.classID',
                    'books.subjectID',
                    'classes.classID',
                    'classes.class_name',
                    'school_subjects.subjectID',
                    'school_subjects.subject_name'
                )
                ->select(
                    'books.classID',
                    'books.subjectID',
                    'classes.class_name',
                    'school_subjects.subject_name',
                    DB::raw('COUNT(books.bookID) as total'),
                    DB::raw('SUM(CASE WHEN bb.borrowID IS NULL THEN 1 ELSE 0 END) as available'),
                    DB::raw('SUM(CASE WHEN bb.borrowID IS NOT NULL THEN 1 ELSE 0 END) as issued'),
                    DB::raw('COUNT(books.bookID) as book_count')
                )
                ->get();

            $booksByClassSubject = $stats->map(function($row) {
                return [
                    'class' => $row->classID ? ['classID' => $row->classID, 'class_name' => $row->class_name] : null,
                    'subject' => $row->subjectID ? ['subjectID' => $row->subjectID, 'subject_name' => $row->subject_name] : null,
                    'total' => (int) $row->total,
                    'available' => (int) $row->available,
                    'issued' => (int) $row->issued,
                    'book_count' => (int) $row->book_count
                ];
            })->values();

            return response()->json([
                'success' => true,
                'books_by_class_subject' => $booksByClassSubject
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting book statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_book_losses(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $lostBy = $request->input('lost_by');

            $query = BookLoss::with(['book', 'student.subclass.class'])
                ->whereHas('book', function($q) use ($schoolID) {
                    $q->where('schoolID', $schoolID);
                })
                ->where('status', 'lost');

            if ($lostBy && in_array($lostBy, ['student', 'other'])) {
                $query->where('lost_by', $lostBy);
            }

            $losses = $query->orderBy('reported_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'losses' => $losses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load lost books: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store_book_loss(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'isbn' => 'required|string|max:50',
                'lost_by' => 'required|in:student,other',
                'studentID' => 'nullable|exists:students,studentID',
                'description' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $book = Book::where('isbn', $request->isbn)
                ->where('schoolID', $schoolID)
                ->first();
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found.'
                ], 404);
            }

            if ($request->lost_by === 'student' && !$request->studentID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is required.'
                ], 422);
            }

            $exists = BookLoss::where('bookID', $book->bookID)
                ->where('status', 'lost')
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book is already marked as lost.'
                ], 400);
            }

            $loss = BookLoss::create([
                'bookID' => $book->bookID,
                'studentID' => $request->lost_by === 'student' ? $request->studentID : null,
                'lost_by' => $request->lost_by,
                'description' => $request->description ?? null,
                'status' => 'lost',
                'reported_date' => Carbon::now()->toDateString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lost book recorded successfully',
                'loss' => $loss
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record lost book: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_book_damages(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $damagedBy = $request->input('damaged_by');

            $query = BookDamage::with(['book', 'student.subclass.class'])
                ->whereHas('book', function($q) use ($schoolID) {
                    $q->where('schoolID', $schoolID);
                })
                ->where('status', 'damaged');

            if ($damagedBy && in_array($damagedBy, ['student', 'other'])) {
                $query->where('damaged_by', $damagedBy);
            }

            $damages = $query->orderBy('reported_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'damages' => $damages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load damaged books: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store_book_damage(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'isbn' => 'required|string|max:50',
                'damaged_by' => 'required|in:student,other',
                'studentID' => 'nullable|exists:students,studentID',
                'description' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $book = Book::where('isbn', $request->isbn)
                ->where('schoolID', $schoolID)
                ->first();
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found.'
                ], 404);
            }

            if ($request->damaged_by === 'student' && !$request->studentID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is required.'
                ], 422);
            }

            $exists = BookDamage::where('bookID', $book->bookID)
                ->where('status', 'damaged')
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book is already marked as damaged.'
                ], 400);
            }

            $damage = BookDamage::create([
                'bookID' => $book->bookID,
                'studentID' => $request->damaged_by === 'student' ? $request->studentID : null,
                'damaged_by' => $request->damaged_by,
                'description' => $request->description ?? null,
                'status' => 'damaged',
                'reported_date' => Carbon::now()->toDateString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Damaged book recorded successfully',
                'damage' => $damage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record damaged book: ' . $e->getMessage()
            ], 500);
        }
    }

    public function send_parent_message(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'studentID' => 'required|exists:students,studentID',
                'message' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $student = Student::with('parent')->where('studentID', $request->studentID)
                ->where('schoolID', $schoolID)
                ->first();
            if (!$student || !$student->parent || !$student->parent->phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent phone not found.'
                ], 404);
            }

            $smsService = new SmsService();
            $result = $smsService->sendSms($student->parent->phone, $request->message);

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'SMS sent'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_book_loss_payment(Request $request, $lossID)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|in:replace,cash',
                'payment_amount' => 'nullable|numeric|min:0'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $loss = BookLoss::where('lossID', $lossID)
                ->whereHas('book', function($q) use ($schoolID) {
                    $q->where('schoolID', $schoolID);
                })
                ->first();

            if (!$loss) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loss record not found.'
                ], 404);
            }

            $loss->payment_status = 'paid';
            $loss->payment_method = $request->payment_method;
            $loss->payment_amount = $request->payment_method === 'cash' ? $request->payment_amount : null;
            $loss->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_book_damage_payment(Request $request, $damageID)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|in:replace,cash',
                'payment_amount' => 'nullable|numeric|min:0'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $damage = BookDamage::where('damageID', $damageID)
                ->whereHas('book', function($q) use ($schoolID) {
                    $q->where('schoolID', $schoolID);
                })
                ->first();

            if (!$damage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Damage record not found.'
                ], 404);
            }

            $damage->payment_status = 'paid';
            $damage->payment_method = $request->payment_method;
            $damage->payment_amount = $request->payment_method === 'cash' ? $request->payment_amount : null;
            $damage->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_library_students(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session.'
                ], 400);
            }

            $filter = $request->input('filter');
            $items = collect();

            $resolvePhoto = function($student) {
                if (!$student || !$student->photo) {
                    return $student && $student->gender == 'Female'
                        ? asset('images/female.png')
                        : asset('images/male.png');
                }
                $photoPath = public_path('userImages/' . $student->photo);
                if (file_exists($photoPath)) {
                    return asset('userImages/' . $student->photo);
                }
                return $student->gender == 'Female'
                    ? asset('images/female.png')
                    : asset('images/male.png');
            };

            if (in_array($filter, ['occupied', 'overdue', 'not_due'])) {
                $query = BookBorrow::with(['student.subclass.class', 'book'])
                    ->whereHas('book', function($q) use ($schoolID) {
                        $q->where('schoolID', $schoolID);
                    })
                    ->where('status', 'borrowed');

                if ($filter === 'overdue') {
                    $query->whereNotNull('expected_return_date')
                        ->where('expected_return_date', '<', Carbon::now()->toDateString());
                }
                if ($filter === 'not_due') {
                    $query->whereNotNull('expected_return_date')
                        ->where('expected_return_date', '>=', Carbon::now()->toDateString());
                }

                $items = $query->orderBy('borrow_date', 'desc')->get()->map(function($borrow) use ($filter, $resolvePhoto) {
                    $student = $borrow->student;
                    $className = $student && $student->subclass && $student->subclass->class
                        ? $student->subclass->class->class_name
                        : '-';
                    return [
                        'type' => $filter,
                        'borrowID' => $borrow->borrowID,
                        'student' => [
                            'studentID' => $student ? $student->studentID : null,
                            'name' => $student ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) : '-',
                            'admission_number' => $student ? ($student->admission_number ?? '-') : '-',
                            'class_name' => $className,
                            'photo' => $resolvePhoto($student),
                            'gender' => $student ? ($student->gender ?? null) : null
                        ],
                        'book' => [
                            'title' => $borrow->book ? $borrow->book->book_title : '-',
                            'isbn' => $borrow->book ? $borrow->book->isbn : '-'
                        ],
                        'expected_return_date' => $borrow->expected_return_date
                    ];
                });
            }

            if ($filter === 'lost') {
                $items = BookLoss::with(['student.subclass.class', 'book'])
                    ->whereHas('book', function($q) use ($schoolID) {
                        $q->where('schoolID', $schoolID);
                    })
                    ->where('status', 'lost')
                    ->orderBy('reported_date', 'desc')
                    ->get()
                    ->map(function($loss) use ($resolvePhoto) {
                        $student = $loss->student;
                        $className = $student && $student->subclass && $student->subclass->class
                            ? $student->subclass->class->class_name
                            : '-';
                        return [
                            'type' => 'lost',
                            'lossID' => $loss->lossID,
                            'student' => [
                                'studentID' => $student ? $student->studentID : null,
                                'name' => $student ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) : '-',
                                'admission_number' => $student ? ($student->admission_number ?? '-') : '-',
                                'class_name' => $className,
                                'photo' => $resolvePhoto($student),
                                'gender' => $student ? ($student->gender ?? null) : null
                            ],
                            'book' => [
                                'title' => $loss->book ? $loss->book->book_title : '-',
                                'isbn' => $loss->book ? $loss->book->isbn : '-'
                            ],
                            'payment_status' => $loss->payment_status,
                            'payment_method' => $loss->payment_method,
                            'payment_amount' => $loss->payment_amount
                        ];
                    });
            }

            if ($filter === 'damaged') {
                $items = BookDamage::with(['student.subclass.class', 'book'])
                    ->whereHas('book', function($q) use ($schoolID) {
                        $q->where('schoolID', $schoolID);
                    })
                    ->where('status', 'damaged')
                    ->orderBy('reported_date', 'desc')
                    ->get()
                    ->map(function($damage) use ($resolvePhoto) {
                        $student = $damage->student;
                        $className = $student && $student->subclass && $student->subclass->class
                            ? $student->subclass->class->class_name
                            : '-';
                        return [
                            'type' => 'damaged',
                            'damageID' => $damage->damageID,
                            'student' => [
                                'studentID' => $student ? $student->studentID : null,
                                'name' => $student ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) : '-',
                                'admission_number' => $student ? ($student->admission_number ?? '-') : '-',
                                'class_name' => $className,
                                'photo' => $resolvePhoto($student),
                                'gender' => $student ? ($student->gender ?? null) : null
                            ],
                            'book' => [
                                'title' => $damage->book ? $damage->book->book_title : '-',
                                'isbn' => $damage->book ? $damage->book->isbn : '-'
                            ],
                            'payment_status' => $damage->payment_status,
                            'payment_method' => $damage->payment_method,
                            'payment_amount' => $damage->payment_amount
                        ];
                    });
            }

            return response()->json([
                'success' => true,
                'total' => $items->count(),
                'items' => $items
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading library students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load students: ' . $e->getMessage()
            ], 500);
        }
    }
}
