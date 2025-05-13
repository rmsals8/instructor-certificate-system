<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateCareerDetail extends Model
{
    protected $fillable = [
        'certificate_id',
        'career_record_id',
    ];

    public function certificate()
    {
        return $this->belongsTo(CareerCertificate::class, 'certificate_id');
    }

    public function careerRecord()
    {
        return $this->belongsTo(CareerRecord::class);
    }
}
