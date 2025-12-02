<?php

namespace Motor\Core\Http\Controllers\Api\V2;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Motor\Core\Http\Traits\V2\HandlesApiErrors;

abstract class ApiController extends Controller
{
    use HandlesApiErrors;

    /**
     * Return 204 No Content response for successful deletes.
     */
    protected function noContentResponse(): Response
    {
        return response()->noContent();
    }
}
