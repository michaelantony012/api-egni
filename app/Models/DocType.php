<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocType extends Model
{
    use HasFactory;

    protected $table = 'document_type';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['id', 'doctype_no', 'doctype_desc'];
    // protected $hidden = [];
    // protected $dates = [];
}
