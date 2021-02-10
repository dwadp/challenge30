<?php

namespace App\Controllers;

use Core\Controller\BaseController;
use App\Models\Post;

class PostController extends BaseController
{
    /**
     * Index page
     *
     * @return void
     */
    public function index()
    {
        $posts = Post::where('id', '>', 2)->get();

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
        $request = $this->request->only(['title', 'body']);

        $this->validate($request);

        if (($this->validate($request)) &&
            (!$this->request->empty())) {
            Post::create($request);

            return $this->redirect('/');
        }
    }
}