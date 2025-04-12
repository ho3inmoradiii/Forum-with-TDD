<?php

namespace App\Models;

use App\Filters\ThreadFilter;
use App\Observers\ThreadObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::observe(ThreadObserver::class);
    }

    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'user_id',
        'channel_id'
    ];

    public function scopeFilter($query, ThreadFilter $filters)
    {
        return $filters->apply($query);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addReply($reply)
    {
        if (is_array($reply)) {
            return $this->replies()->create($reply);
        }

        if (is_object($reply) && method_exists($reply, 'toArray')) {
            return $this->replies()->create($reply->toArray());
        }
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'target');
    }
}
