<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServerSize extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'ram', 'cpu', 'disk', 'transfer', 'dollars_per_hr', 'dollars_per_mo',
    ];

    /**
     * The attributes that should be mutated to other formats.
     *
     * @var array
     */
    protected $casts = [
        'ram'            => 'integer',
        'cpu'            => 'integer',
        'disk'           => 'integer',
        'transfer'       => 'real',
        'dollars_per_hr' => 'real',
        'dollars_per_mo' => 'real',
    ];

    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    public function serverPlans()
    {
        return $this->belongsToMany(ServerPlan::class)->withTimestamps();
    }
}
