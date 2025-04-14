<?php

namespace App\Models;

use App\Observers\ReplyObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::observe(ReplyObserver::class);
    }

    protected $fillable = ['body', 'user_id'];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_replies')->withTimestamps();
    }

    public function isFavoritedByCurrentUser()
    {
        return $this->favoritedBy()->where('user_id', auth()->id())->exists();
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'target');
    }
}
