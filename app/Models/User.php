<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'birth_date',
        'phone_number',
        'email',
        'address',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
        ];
    }

    public function careerRecords()
    {
        return $this->hasMany(CareerRecord::class);
    }

    public function careerCertificates()
    {
        return $this->hasMany(CareerCertificate::class);
    }

    public function salaryStatements()
    {
        return $this->hasMany(SalaryStatement::class);
    }

    public function paymentInformation()
    {
        return $this->hasMany(PaymentInformation::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
