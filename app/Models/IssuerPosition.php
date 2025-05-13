<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssuerPosition extends Model
{
    protected $fillable = [
        'title',
        'name',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function careerCertificates()
    {
        return $this->hasMany(CareerCertificate::class);
    }

    public function isActive()
    {
        return $this->end_date === null || $this->end_date >= now();
    }
}
