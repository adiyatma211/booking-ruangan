<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class pemesanan extends Model
{
    /** @use HasFactory<\Database\Factories\PemesananFactory> */
    use HasFactory,SoftDeletes;


    protected $guarded=['id'];


}
