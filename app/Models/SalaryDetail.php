<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryDetail extends Model
{
    protected $fillable = [
        'salary_statement_id',
        'per_student_fee',
        'student_count',
        'subsidy_amount',
        'additional_payment',
        'cancellation_refund',
        'other_refund',
        'total_amount',
    ];

    protected $casts = [
        'per_student_fee' => 'decimal:2',
        'subsidy_amount' => 'decimal:2',
        'additional_payment' => 'decimal:2',
        'cancellation_refund' => 'decimal:2',
        'other_refund' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function salaryStatement()
    {
        return $this->belongsTo(SalaryStatement::class);
    }

    // 총액 계산 메소드
    public function calculateTotalAmount()
    {
        return ($this->per_student_fee * $this->student_count) +
               $this->subsidy_amount +
               $this->additional_payment -
               $this->cancellation_refund -
               $this->other_refund;
    }
}
