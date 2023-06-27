<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sales extends Model
{
    use HasFactory;
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'sales_invoice_h';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function salesDetail()
    {
        return $this->hasMany(SalesDetail::class, 'id_header');
    }

    public function salesReturn()
    {
        return $this->hasMany(SalesReturn::class, 'id_header');
    }

    /**
     * Get the supplier associated with the Purchasing
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    /**
     * Get the user that owns the Purchasing
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }
    // public function docflow(): BelongsTo
    // {
    //     return $this->belongsTo(DocFlow::class, ['5', 'doctype_id'], ['flow_seq', 'doc_flow']);
    // }
    // public function userRelations() {
    //     return $this->hasMany('App\UserRelation');
    // }

    // public function relatedUserRelations() {
    //     return $this->hasMany('App\UserRelation', 'related_user_id');
    // }

    // public function allUserRelations() {
    //     return $this->userRelations->merge($this->relatedUserRelations);
    // }
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
