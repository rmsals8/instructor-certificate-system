<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function careerRecords()
    {
        return $this->hasMany(CareerRecord::class);
    }
}
