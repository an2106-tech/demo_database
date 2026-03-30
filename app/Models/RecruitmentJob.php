<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentJob extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'slug',
        'branch_id',
        'workplace_id',
        'description',
        'status',
        'salary_range',
        'deadline',
        'positions_count',
        'public_url',
        'thumbnail',
        'department_id',
        'created_by',
    ];

    protected $casts = [
        'salary_range' => 'array',
        'deadline' => 'date',
    ];

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function workplace() {
        return $this->belongsTo(Workplace::class);
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
