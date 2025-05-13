<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeductionDetail extends Model
{
    protected $fillable = [
        'salary_statement_id',
        'industrial_insurance',
        'employment_insurance',
        'income_tax',
        'local_income_tax',
        'other_deduction',
        'total_deduction',
    ];

    protected $casts = [
        'industrial_insurance' => 'decimal:2',
        'employment_insurance' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'local_income_tax' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'total_deduction' => 'decimal:2',
    ];

    public function salaryStatement()
    {
        return $this->belongsTo(SalaryStatement::class);
    }

    // 총 공제액 계산 메소드
    public function calculateTotalDeduction()
    {
        return $this->industrial_insurance +
               $this->employment_insurance +
               $this->income_tax +
               $this->local_income_tax +
               $this->other_deduction;
    }
}
