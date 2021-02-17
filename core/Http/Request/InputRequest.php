<?php

namespace Core\Http\Request;

class InputRequest extends RequestCollection
{
    /**
     * Capture all 'POST' request
     *
     * @return void
     */
    public function capture()
    {
        $this->append($_POST);
    }
}