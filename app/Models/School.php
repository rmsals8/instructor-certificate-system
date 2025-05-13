<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'name',
        'address',
        'type',
        'region',
    ];

    public function careerRecords()
    {
        return $this->hasMany(CareerRecord::class);
    }

    public function salaryStatements()
    {
        return $this->hasMany(SalaryStatement::class);
    }
}
