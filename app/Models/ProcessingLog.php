<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessingLog extends Model
{
    use HasFactory;

    protected $fillable = ['debtId', 'message'];
}
