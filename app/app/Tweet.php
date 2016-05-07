<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    protected $fillable = [
        'tweet_id',
        'tweet_text',
        'youtube_url',
        'youtube_id',
    ];
}
