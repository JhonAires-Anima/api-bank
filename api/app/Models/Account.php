<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $connection='bank';
    protected $table='accounts';
    protected $primaryKey = 'id';
    public $timestamps=false;
}
