<?php

namespace App\Models;

use DateTime;
use App\Core\Database\Model;

class Post extends Model
{
    /**
     * Define what table in the database should this model use
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * Define columns cast
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'date'
    ];
    
    /**
     * Store a post to the database
     *
     * @param array $data
     * @return void
     */
    public function store($data)
    {
        $now                = date('Y-m-d H:i:s');
        $data['created_at'] = $now;

        $this->insert($data);
    }

    /**
     * Get all posts in descending order
     *
     * @return array
     */
    public function all()
    {
        $posts = $this->select()->order('created_at', 'desc')->get();

        return $posts;
    }
}