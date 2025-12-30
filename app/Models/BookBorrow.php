<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookBorrow extends Model
{
    use HasFactory;

    protected $table = 'book_borrows';
    protected $primaryKey = 'borrowID';

    protected $fillable = [
        'bookID',
        'studentID',
        'borrow_date',
        'expected_return_date',
        'return_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'expected_return_date' => 'date',
        'return_date' => 'date',
        'status' => 'string',
    ];

    // Relationships
    public function book()
    {
        return $this->belongsTo(Book::class, 'bookID', 'bookID');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }
}
