<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Motor\Admin\Models\Client;
use Motor\Admin\Models\User;
use Motor\Core\Test\Fixtures\Http\Requests\ValidatesAgainstUserClientsFixtureRequest;

uses(RefreshDatabase::class);

function buildFixtureRequest(User $user, array $payload): ValidatesAgainstUserClientsFixtureRequest
{
    $request = ValidatesAgainstUserClientsFixtureRequest::create('/test', 'POST', $payload);
    $request->setContainer(app());
    $request->setRedirector(app(Redirector::class));
    $request->setUserResolver(fn () => $user);

    return $request;
}

describe('ValidatesAgainstUserClients trait', function () {

    it('accepts a client_id that is in the user\'s pivot', function () {
        $client = Client::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $user->clients()->attach($client->id);

        $validator = Validator::make(
            ['client_id' => $client->id],
            buildFixtureRequest($user, [])->rules()
        );

        expect($validator->fails())->toBeFalse();
    });

    it('rejects a client_id that is not in the user\'s pivot', function () {
        $own = Client::factory()->create();
        $foreign = Client::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $user->clients()->attach($own->id);

        $request = buildFixtureRequest($user, ['client_id' => $foreign->id]);

        $validator = Validator::make(
            ['client_id' => $foreign->id],
            $request->rules()
        );

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('client_id'))->toBeTrue();
    });

    it('lets a SuperAdmin target any seeded client', function () {
        Client::factory()->count(3)->create();
        $target = Client::query()->inRandomOrder()->first();

        $admin = User::factory()->create();
        $admin->assignRole('SuperAdmin');
        // No pivot attachments. SuperAdmin is allowed to target any seeded client.

        $request = buildFixtureRequest($admin, ['client_id' => $target->id]);

        $validator = Validator::make(
            ['client_id' => $target->id],
            $request->rules()
        );

        expect($validator->fails())->toBeFalse();
    });

    it('rejects every value for an empty-pivot non-SuperAdmin user', function () {
        $any = Client::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Editor');
        // No pivot attachments.

        $request = buildFixtureRequest($user, ['client_id' => $any->id]);

        $validator = Validator::make(
            ['client_id' => $any->id],
            $request->rules()
        );

        expect($validator->fails())->toBeTrue();
    });
});
