<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UserDepositController extends Controller
{
    private $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Display a listing of the resource.
     *
     * @param User $user
     * @return Response
     */
    public function index(User $user)
    {
        return $this->responseFactory->view("user-deposit.index", [
            "user" => $user,
            "deposits" => $user->deposits()->paginate()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param User $user
     * @return Response
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, User $user)
    {
        $data = $request->validate([
            "amount" => ["required", "numeric", "gte:0"],
        ]);

        DB::beginTransaction();

        // Deposit biasa
        $deposit = $user->deposits()->create([
            "amount" => $data["amount"],
            "type" => "REGULAR"
        ]);

        $user->load([
            "parent_ref.parent_user",
            "children_refs.child_user"
        ]);

        if ($user->parent_ref !== null) {
             $user->parent_ref->parent_user->deposits()->create([
                "amount" => $deposit->amount * 0.1,
                "type" => "BONUS",
            ]);
        }

        foreach ($user->children_refs as $children_ref) {
            $children_ref->child_user->deposits()->create([
                "amount" => $deposit->amount * 0.1,
                "type" => "BONUS",
            ]);
        }

        DB::commit();

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @param Deposit $deposit
     * @return Response
     */
    public function show(User $user, Deposit $deposit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @param Deposit $deposit
     * @return Response
     */
    public function edit(User $user, Deposit $deposit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @param Deposit $deposit
     * @return Response
     */
    public function update(Request $request, User $user, Deposit $deposit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @param Deposit $deposit
     * @return Response
     */
    public function destroy(User $user, Deposit $deposit)
    {
        //
    }
}
