<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Motor\Admin\Models\Client;
use Motor\Admin\Models\User;
use Motor\Core\Filter\Filter;
use Motor\Core\Scopes\ClientScope;

uses(RefreshDatabase::class);

beforeEach(fn () => app()->forgetInstance(ClientScope::RESOLVER_KEY));
afterEach(fn () => app()->forgetInstance(ClientScope::RESOLVER_KEY));

// Phase 7: Filter::addClientFilter() reads the user's first pivot client_id /
// name now that users.client_id has been dropped. SuperAdmin / empty-pivot
// users still see the full multi-client option list.

describe('Filter::addClientFilter pivot migration', function () {

    it('hides the filter and pins to the user\'s first pivot client', function () {
        $client = Client::factory()->create(['name' => 'Acme Co']);
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $user->clients()->attach($client->id);

        $this->actingAs($user);

        $filter = new Filter('Foo');
        $filter->addClientFilter();

        $clientFilter = $filter->get('client_id');

        expect($clientFilter)->not->toBeNull();
        expect($clientFilter->getDefaultValue())->toBe($client->id);
        expect($clientFilter->getOptions())->toBe([$client->id => 'Acme Co']);
    });

    it('shows the full client list for an empty-pivot user', function () {
        Client::factory()->create(['name' => 'Acme Co']);
        Client::factory()->create(['name' => 'Beta Co']);

        $user = User::factory()->create();
        $user->assignRole('Editor');
        // No clients attached.

        $this->actingAs($user);

        $filter = new Filter('Foo');
        $filter->addClientFilter();

        $clientFilter = $filter->get('client_id');

        expect($clientFilter)->not->toBeNull();
        expect($clientFilter->getOptions())->toHaveCount(Client::count());
        expect($clientFilter->getDefaultValue())->toBeNull();
    });

    it('uses only the first pivot row for a multi-client user (V1-strict semantics)', function () {
        $first = Client::factory()->create(['name' => 'First']);
        $second = Client::factory()->create(['name' => 'Second']);

        $user = User::factory()->create();
        $user->assignRole('Editor');
        $user->clients()->attach([$first->id, $second->id]);

        $this->actingAs($user);

        $filter = new Filter('Foo');
        $filter->addClientFilter();

        $clientFilter = $filter->get('client_id');

        expect($clientFilter->getOptions())->toHaveCount(1);
        expect($clientFilter->getDefaultValue())->toBe($first->id);
    });
});
