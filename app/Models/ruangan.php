<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ruangan extends Model
{
    /** @use HasFactory<\Database\Factories\RuanganFactory> */
    use HasFactory,Notifiable, HasRoles,SoftDeletes;

    protected $guarded = ['id'];

}
