<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalNecessity extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'legal_activities', 'status_company', 'value_trx', 'target_output', 'message'];
}
