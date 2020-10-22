<?php

namespace App\Http\Controllers;

use App\Models\Path;
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
                    "parent_ref.ancestor",
                    "children_refs.descendant",
                ])
                ->addSelect([
                    "left_child" => Path::query()
                        ->select("paths.descendant_id")
                        ->whereColumn("paths.ancestor_id", "=", "users.id")
                        ->where("tree_depth", 1)
                        ->orderBy("id")
                        ->limit(1),

                    "right_child" => Path::query()->from(DB::raw("paths paths_rc"))
                        // Anak di sisi kanan hanya ada jika orang tuanya memiliki > 1 anak
                        ->where(
                            Path::query()->from(DB::raw("paths paths_sub"))
                                ->selectRaw("COUNT(*)")
                                ->whereColumn("paths_sub.ancestor_id", "=", "users.id")
                                ->where("tree_depth", 1)
                            , ">", 1)

                        ->select("paths_rc.descendant_id")
                        ->whereColumn("paths_rc.ancestor_id", "=", "users.id")
                        ->where("tree_depth", 1)

                        // Anak di sisi kiri adalah anak yang id nya lebih besar daripada anak lain
                        ->orderByDesc("id")
                        ->limit(1),

                    "left_descendant_count" =>
                        Path::query()
                            ->selectRaw("COUNT(*)")
                            ->whereIn(
                                "ancestor_id",
                                Path::query()
                                    ->selectRaw("MIN(paths.descendant_id)")
                                    ->whereColumn("paths.ancestor_id", "=", "users.id")
                                    ->where("tree_depth", 1)
                            ),

                    "right_descendant_count" =>
                        Path::query()
                            ->selectRaw("COUNT(*)")
                            ->whereIn(
                                "ancestor_id",
                                Path::query()->from(DB::raw("paths paths_rdc"))
                                    ->selectRaw("MAX(paths_rdc.descendant_id)")

                                    // Anak di sisi kanan hanya ada jika orang tuanya memiliki > 1 anak
                                    ->where(
                                        Path::query()->from(DB::raw("paths paths_sub"))
                                            ->selectRaw("COUNT(*)")
                                            ->whereColumn("paths_sub.ancestor_id", "=", "users.id")
                                            ->where("tree_depth", 1)
                                        , ">", 1)

                                    ->whereColumn("paths_rdc.ancestor_id", "=", "users.id")
                                    ->where("tree_depth", 1)
                            ),

                    "left_deposit_total" => User::query()->from(DB::raw("users users_lds"))
                        ->selectRaw("SUM(users_lds.deposit_amount)")
                        ->whereIn(
                            "id",
                            Path::query()
                                ->select("paths.descendant_id")
                                ->whereIn(
                                    "ancestor_id",
                                    Path::query()
                                        ->selectRaw("MIN(paths.descendant_id)")
                                        ->whereColumn("paths.ancestor_id", "=", "users.id")
                                        ->where("tree_depth", 1)
                                )
                        ),

                    "right_deposit_total" => User::query()->from(DB::raw("users users_rds"))
                        ->selectRaw("SUM(users_rds.deposit_amount)")
                        ->whereIn(
                            "id",
                            Path::query()
                                ->selectRaw("paths.descendant_id")
                                ->whereIn(
                                    "ancestor_id",
                                    Path::query()->from(DB::raw("paths paths_rdc"))
                                        ->selectRaw("MAX(paths_rdc.descendant_id)")

                                        // Anak di sisi kanan hanya ada jika orang tuanya memiliki > 1 anak
                                        ->where(
                                            Path::query()->from(DB::raw("paths paths_sub"))
                                                ->selectRaw("COUNT(*)")
                                                ->whereColumn("paths_sub.ancestor_id", "=", "users.id")
                                                ->where("tree_depth", 1)
                                            , ">", 1)

                                        ->whereColumn("paths_rdc.ancestor_id", "=", "users.id")
                                        ->where("tree_depth", 1)
                                )
                        ),
                ])
                ->paginate(30),

            "graph_nodes" => User::query()
                ->has("descendant_refs")
                ->has("ancestor_refs")
                ->select([
                    "id",
                    DB::raw("CONCAT(name, ' (', id, ')', 'DEP: ', COALESCE(deposit_amount, ''))  AS label"),
                    DB::raw("IF(deposited_at IS NOT NULL, '#A2FFF7', '#FFAFA3') AS color"),
                ])->get(),

            "graph_edges" => Path::query()
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
