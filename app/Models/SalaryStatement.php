<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryStatement extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'year',
        'month',
        'payment_date',
        'certificate_number',
        'issued_by',
        'status',
        'pdf_path',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function salaryDetail()
    {
        return $this->hasOne(SalaryDetail::class);
    }

    public function deductionDetail()
    {
        return $this->hasOne(DeductionDetail::class);
    }

    public function getActualPayment()
    {
        $totalAmount = $this->salaryDetail->total_amount ?? 0;
        $totalDeduction = $this->deductionDetail->total_deduction ?? 0;

        return $totalAmount - $totalDeduction;
    }
}
