<?php

namespace Attla;

use Illuminate\Routing\Controller as BaseController;
use Attla\Validation\ValidatesRequests;

class Controller extends BaseController
{
    use ValidatesRequests;
}
