<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'subject', 'marks',
    ];

    public function student()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

}
