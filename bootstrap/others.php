<?php

use App\Core\Registry;
use App\Core\Request;
use App\Core\Url;
use App\Core\Validator\Validator;
use App\Core\View;

Registry::register('validator', new Validator);
Registry::register('url', new Url);
Registry::register('request', new Request);
Registry::register('view', new View);