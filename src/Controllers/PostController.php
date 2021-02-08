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
     * Index page
     *
     * @return void
     */
    public function index()
    {
        $posts = $this->post->all();
        
        $this->store();
        
        $this->view->render('post/index.php', ['posts' => $posts]);
    }

    /**
     * Validate data based on the given rules
     *
     * @param array $data
     * @return boolean
     */
    private function validate($data)
    {
        $rules = [
            'title' => [
                'required'  => true,
                'lengths'   => [10, 32]
            ],
            'body'  => [
                'required'  => true,
                'lengths'   => [10, 200]
            ]
        ];

        $this->validator->validate($rules, $data);

        return $this->validator->error->empty();
    }

    /**
     * Store the requested data to database
     *
     * @return void
     */
    public function store()
    {
        if ($this->request->has('submit')) {
            $request        = $this->request->only(['title', 'body']);
            $validationPass = $this->validate($request);

            if (!$validationPass) {
                return;
            }

            $this->post->store($request);

            return $this->redirect('/');
        }
    }
}