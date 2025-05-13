<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerCertificate extends Model
{
    protected $fillable = [
        'user_id',
        'issue_date',
        'certificate_number',
        'purpose',
        'issuer_position_id',
        'issued_by',
        'pdf_path',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issuerPosition()
    {
        return $this->belongsTo(IssuerPosition::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function certificateCareerDetails()
    {
        return $this->hasMany(CertificateCareerDetail::class, 'certificate_id');
    }

    public function careerRecords()
    {
        return $this->hasManyThrough(
            CareerRecord::class,
            CertificateCareerDetail::class,
            'certificate_id',
            'id',
            'id',
            'career_record_id'
        );
    }
}
