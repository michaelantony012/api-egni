<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocType extends Model
{
    use HasFactory;

    protected $table = 'document_type';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['id', 'doctype_no', 'doctype_desc', 'doctype_table'];
    // protected $hidden = [];
    // protected $dates = [];

    /**
     * Get all of the doc_flow for the DocType
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function doc_flow(): HasMany
    {
        return $this->hasMany(DocFlow::class, 'doctype_id');
    }
    public function doc_flow_logic(): HasMany
    {
        return $this->hasMany(DocFlowLogic::class, 'doctype_id');
    }
}
