<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposits extends Model
{
    use HasFactory;
    protected $connection='bank';
    protected $table='deposits';
    protected $primaryKey = 'id_deposit';
    public $incrementing = true;
    public $timestamps=false;
}
