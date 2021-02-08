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
     * Define columns casts
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
        $data['created_at'] = date('Y-m-d H:i:s');

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