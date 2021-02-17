<?php

namespace Core\Http\Request;

class QueryRequest extends RequestCollection
{
    /**
     * Capture all 'GET' request
     *
     * @return void
     */
    public function capture()
    {
        $this->append($_GET);
    }
}