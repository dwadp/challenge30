<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Post;

class PostController extends BaseController
{
    /**
     * The post model
     *
     * @var App\Models\Post
     */
    public $post;

    public function __construct()
    {
        parent::__construct();
        
        $this->post = new Post;
    }

    /**
     * Handle index page
     *
     * @return void
     */
    public function handleIndex()
    {
        $this->handlePostForm();
        $this->showPosts();
    }

    /**
     * Handle post form submission and save all data to the database
     *
     * @return void
     */
    public function handlePostForm()
    {
        $submitted = isset($_POST['submit']) ? true : false;

        if ($submitted) {
            $rules = [
                'title' => [
                    'required'      => true,
                    'rangechars'    => [
                        'from'  => 10,
                        'to'    => 32
                    ]
                ],
                'body'  => [
                    'required'      => true,
                    'rangechars'    => [
                        'from'  => 10,
                        'to'    => 200
                    ]
                ]
            ];

            $this->validator->validate($rules, $_POST);

            if (!$this->validator->errorsEmpty()) {
                $this->validator->old('title');
                return;
            }

            $this->post->store($this->validator->getRequests());

            return $this->redirect('/');
        }
    }

    /**
     * Get all data from posts table in the database
     *
     * @return array
     */
    public function showPosts()
    {
        $posts = $this->post->all();
        
        $this->view('post/index.php', ['posts' => $posts]);
    }
}