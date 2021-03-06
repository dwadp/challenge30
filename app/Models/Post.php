<?php

namespace App\Models;

use Core\Database\Model;

class Post extends Model
{
    /**
     * Define what table in the database should this model use
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * Whitelist the table columns
     *
     * @var array
     */
    protected $fillable = [
        'title', 'body'
    ];
}