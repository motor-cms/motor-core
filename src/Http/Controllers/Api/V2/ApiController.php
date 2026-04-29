<?php

namespace Motor\Core\Http\Controllers\Api\V2;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Motor\Core\Http\Traits\V2\HandlesApiErrors;

abstract class ApiController extends Controller
{
    use AuthorizesRequests;
    use HandlesApiErrors;

    /**
     * The model class for authorization.
     */
    protected string $model = '';

    /**
     * The model resource name for authorization.
     */
    protected string $modelResource = '';

    public function __construct()
    {
        if ($this->model && $this->modelResource) {
            $this->authorizeResource($this->model, $this->modelResource);
        }
        \Locale::setDefault(config('app.locale'));
    }

    /**
     * Return 204 No Content response for successful deletes.
     */
    protected function noContentResponse(): Response
    {
        return response()->noContent();
    }
}
