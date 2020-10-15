<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserHomeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return Application|Factory|View|Response
     */
    public function __invoke(Request $request, User $user)
    {
        return view("user-home", [
            "user" => $user,
            "deposit_types" => User::DEPOSIT_TYPES,
        ]);
    }
}
