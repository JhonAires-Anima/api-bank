<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawals extends Model
{
    use HasFactory;
    protected $connection='bank';
    protected $table='withdrawals';
    protected $primaryKey = 'id_withdrawal';
    public $incrementing = true;
    public $timestamps=false;
}
