<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CareerRecord extends Model
{
    protected $fillable = [
        'user_id',
        'instructor_type_id',
        'subject_id',
        'school_id',
        'start_date',
        'end_date',
        'hours_per_week',
        'position',
        'description',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function instructorType()
    {
        return $this->belongsTo(InstructorType::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function certificateCareerDetails()
    {
        return $this->hasMany(CertificateCareerDetail::class);
    }

    public function getDurationInDays()
    {
        $endDate = $this->end_date ?? Carbon::today();
        return $this->start_date->diffInDays($endDate) + 1; // +1 to include start date
    }

    public function getDurationText()
    {
        $endDate = $this->end_date ?? Carbon::today();
        $diff = $this->start_date->diff($endDate);

        $years = $diff->y;
        $months = $diff->m;
        $days = $diff->d;

        $result = [];
        if ($years > 0) {
            $result[] = $years . '년';
        }
        if ($months > 0) {
            $result[] = $months . '개월';
        }
        if ($days > 0 && count($result) == 0) {
            $result[] = $days . '일';
        }

        return implode(' ', $result);
    }
}
