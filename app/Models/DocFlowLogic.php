<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocFlowLogic extends Model
{
    use HasFactory;

    protected $table = 'document_flow_logic';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['doctype_id', 'flow_prev', 'flow_next', 'flow_desc'];
    // protected $hidden = [];
    // protected $dates = [];

    /**
     * Get the doc_type that owns the DocFlowLogic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doc_type(): BelongsTo
    {
        return $this->belongsTo(DocType::class, 'doctype_id');
    }
}
