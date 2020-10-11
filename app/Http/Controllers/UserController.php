<?php

namespace App\Http\Controllers;

use App\Models\ReferralPath;
use App\Models\User;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return $this->responseFactory->view("user.index", [
            "users" => User::query()
                ->orderBy("id")
                ->with([
                    "parent_ref.parent_user",
                    "children_refs.child_user",
                ])
                ->paginate(),

            "graph_nodes" => User::query()
                ->has("descendant_refs")
                ->has("ancestor_refs")
                ->select([
                    "id",
                    "name AS label",
                ])->get(),

            "graph_edges" => ReferralPath::query()
                ->select([
                    "ancestor_id AS to",
                    "descendant_id AS from",
                ])
                ->where("tree_depth", 1)
                ->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\User $user
     * @return Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\User $user
     * @return Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param \App\User $user
     * @return Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\User $user
     * @return Response
     */
    public function destroy(User $user)
    {
        //
    }
}
