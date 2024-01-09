<?php

namespace Motor\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class ArtisanCommandsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('token') !== config('ekpro.ekpro_token')) {
            abort(404);
        }

        $commands = Artisan::all();

        return view('motor-core::artisan_commands.index', compact('commands'));
    }

    public function execute(Request $request, $command)
    {
        if ($request->get('token') !== config('ekpro.ekpro_token')) {
            abort(404);
        }

        $params = $request->except(['token']);
        Artisan::call($command, $params);

        return Artisan::output();
    }
}
