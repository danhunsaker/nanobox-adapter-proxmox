<?php

namespace App;

use Crypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Encryption\DecryptException;

class Server extends Model
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
        'name', 'password', 'node', 'storage',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    // Relationships

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function serverSize()
    {
        return $this->belongsTo(ServerSize::class);
    }

    public function key()
    {
        return $this->belongsTo(Key::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Getters / Mutators

    public function getPasswordAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (DecryptException $e) {
            return null;
        }
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = empty($value) ? null : Crypt::encrypt($value);
    }
}
