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

            // Validate input
            $validator = Validator::make($request->all(), [
                'classID' => 'required|exists:classes,classID',
                'subjectID' => 'required|exists:school_subjects,subjectID',
                'book_title' => 'required|string|max:255',
                'author' => 'nullable|string|max:255',
                'isbn' => 'nullable|string|max:50',
                'publisher' => 'nullable|string|max:255',
                'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'total_quantity' => 'required|integer|min:1',
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
                'isbn' => $request->isbn ?? null,
                'publisher' => $request->publisher ?? null,
                'publication_year' => $request->publication_year ?? null,
                'total_quantity' => $request->total_quantity,
                'available_quantity' => $request->total_quantity, // Initially all books are available
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

            // Validate input
            $validator = Validator::make($request->all(), [
                'classID' => 'required|exists:classes,classID',
                'subjectID' => 'required|exists:school_subjects,subjectID',
                'book_title' => 'required|string|max:255',
                'author' => 'nullable|string|max:255',
                'isbn' => 'nullable|string|max:50',
                'publisher' => 'nullable|string|max:255',
                'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'total_quantity' => 'required|integer|min:1',
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

            // Calculate new available quantity based on new total_quantity
            $oldTotalQuantity = $book->total_quantity;
            $newTotalQuantity = $request->total_quantity;
            $issuedQuantity = $book->issued_quantity;
            
            // If total quantity increased, add to available
            // If total quantity decreased, adjust available (but not below 0)
            $newAvailableQuantity = max(0, $newTotalQuantity - $issuedQuantity);

            // Update book
            $book->update([
                'classID' => $request->classID,
                'subjectID' => $request->subjectID,
                'book_title' => $request->book_title,
                'author' => $request->author ?? null,
                'isbn' => $request->isbn ?? null,
                'publisher' => $request->publisher ?? null,
                'publication_year' => $request->publication_year ?? null,
                'total_quantity' => $newTotalQuantity,
                'available_quantity' => $newAvailableQuantity,
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
                'bookID' => 'required|exists:books,bookID',
                'studentID' => 'required|exists:students,studentID',
                'expected_return_date' => 'nullable|date|after_or_equal:today',
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
            $book = Book::where('bookID', $request->bookID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found or does not belong to this school.'
                ], 404);
            }

            // Check if book is available
            if ($book->available_quantity <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book is not available. All copies are currently borrowed.'
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
            $existingBorrow = BookBorrow::where('bookID', $request->bookID)
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
                'bookID' => $request->bookID,
                'studentID' => $request->studentID,
                'borrow_date' => Carbon::now()->toDateString(),
                'expected_return_date' => $request->expected_return_date ? Carbon::parse($request->expected_return_date)->toDateString() : null,
                'status' => 'borrowed',
                'notes' => $request->notes ?? null
            ]);

            // Update book quantities
            $book->available_quantity = max(0, $book->available_quantity - 1);
            $book->issued_quantity = $book->issued_quantity + 1;
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

    public function return_book($borrowID)
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

            // Update borrow record
            $borrow->status = 'returned';
            $borrow->return_date = Carbon::now()->toDateString();
            $borrow->save();

            // Update book quantities
            $book = $borrow->book;
            $book->available_quantity = $book->available_quantity + 1;
            $book->issued_quantity = max(0, $book->issued_quantity - 1);
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

            // Build query
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

            $books = $query->get();

            // Group by class and subject
            $booksByClassSubject = $books->groupBy(function($book) {
                return ($book->class ? $book->class->classID : 'no-class') . '-' . ($book->subject ? $book->subject->subjectID : 'no-subject');
            })->map(function($group, $key) {
                $firstBook = $group->first();
                return [
                    'class' => $firstBook->class,
                    'subject' => $firstBook->subject,
                    'total_books' => $group->sum('total_quantity'),
                    'available_books' => $group->sum('available_quantity'),
                    'issued_books' => $group->sum('issued_quantity'),
                    'book_count' => $group->count()
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
}
