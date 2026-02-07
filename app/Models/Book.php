<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $table = 'books';
    protected $primaryKey = 'bookID';

    protected $fillable = [
        'schoolID',
        'classID',
        'subjectID',
        'book_title',
        'author',
        'isbn',
        'publisher',
        'publication_year',
        'total_quantity',
        'available_quantity',
        'issued_quantity',
        'description',
        'status',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'total_quantity' => 'integer',
        'available_quantity' => 'integer',
        'issued_quantity' => 'integer',
        'status' => 'string',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function subject()
    {
        return $this->belongsTo(SchoolSubject::class, 'subjectID', 'subjectID');
    }

    public function borrows()
    {
        return $this->hasMany(BookBorrow::class, 'bookID', 'bookID');
    }

    public function activeBorrows()
    {
        return $this->hasMany(BookBorrow::class, 'bookID', 'bookID')->where('status', 'borrowed');
    }

    public function losses()
    {
        return $this->hasMany(BookLoss::class, 'bookID', 'bookID');
    }

    public function damages()
    {
        return $this->hasMany(BookDamage::class, 'bookID', 'bookID');
    }

    public function activeLosses()
    {
        return $this->hasMany(BookLoss::class, 'bookID', 'bookID')->where('status', 'lost');
    }

    public function activeDamages()
    {
        return $this->hasMany(BookDamage::class, 'bookID', 'bookID')->where('status', 'damaged');
    }
}
