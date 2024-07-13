<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'governmentId', 'email', 'debtAmount', 'debtDueDate', 'debtId', 'boleto_generated', 'email_sent'
    ];

    protected $casts = [
        'boleto_generated' => 'boolean',
        'email_sent' => 'boolean',
    ];
}
