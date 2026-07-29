<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentContact extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'student_id',
        'parent_name',
        'gmail',
        'relationship',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
