<?php

namespace Motor\Core\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

/**
 * Constrains a `client_id` payload field to the request user's allowed clients.
 *
 * Use in V2 Form Request classes that accept a `client_id` for a tenanted
 * model. Compose with the existing rule list for the field:
 *
 *   $rules['client_id'] = array_merge($rules['client_id'] ?? ['nullable', 'integer'], [
 *       $this->allowedClientIdsRule(),
 *   ]);
 *
 * SuperAdmin (`User::isAdmin()`) is allowed to target any seeded client.
 * Every other authenticated user is restricted to the client_ids in their
 * pivot. An empty-pivot non-SuperAdmin user sees `Rule::in([])`, which
 * rejects any value — the BelongsToClient auto-fill also no-ops in that
 * state, so the create path is intentionally blocked end-to-end. V1
 * controllers do not use V2 Form Requests, so V1 behavior is unaffected.
 */
trait ValidatesAgainstUserClients
{
    protected function allowedClientIdsRule(): In
    {
        $user = $this->user();

        if ($user?->isAdmin()) {
            $clientClass = config('motor-admin.models.client');
            $ids = $clientClass::query()->pluck('id')->all();
        } else {
            $ids = $user?->clients->pluck('id')->all() ?? [];
        }

        return Rule::in($ids);
    }
}
