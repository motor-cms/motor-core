<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Motor\Admin\Models\Client;
use Motor\Admin\Models\User;
use Motor\Core\Scopes\ClientScope;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

afterEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

describe('actingAsClientScopedUser', function () {

    it('binds a null resolver for SuperAdmin', function () {
        $admin = User::factory()->create();
        $admin->assignRole('SuperAdmin');

        actingAsClientScopedUser($admin);

        $resolver = app(ClientScope::RESOLVER_KEY);
        expect($resolver())->toBeNull();
    });

    it('binds the user pivot client ids for regular users', function () {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $user->clients()->attach([$clientA->id, $clientB->id]);

        actingAsClientScopedUser($user);

        $resolver = app(ClientScope::RESOLVER_KEY);
        expect($resolver())->toEqualCanonicalizing([$clientA->id, $clientB->id]);
    });

    it('binds an empty array for users with no clients', function () {
        $user = User::factory()->create();
        $user->assignRole('Editor');

        actingAsClientScopedUser($user);

        $resolver = app(ClientScope::RESOLVER_KEY);
        expect($resolver())->toBe([]);
    });

    it('returns the user for chaining', function () {
        $user = User::factory()->create();
        $user->assignRole('Editor');

        $returned = actingAsClientScopedUser($user);

        expect($returned)->toBe($user);
    });

    it('also calls actingAs so auth() returns the user', function () {
        $user = User::factory()->create();
        $user->assignRole('Editor');

        actingAsClientScopedUser($user);

        expect(auth()->user()?->id)->toBe($user->id);
    });
});
