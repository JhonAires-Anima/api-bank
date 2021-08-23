<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfers extends Model
{
    use HasFactory;
    protected $connection='bank';
    protected $table='transfers';
    protected $primaryKey = 'id_transfer';
    public $incrementing = true;
    public $timestamps=false;
}
