<?php

namespace Attla;

use Attla\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use ValidatesRequests;
}
