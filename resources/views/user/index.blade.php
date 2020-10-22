@extends("layouts.app")

@section("content")
    <div id="network" style="width: 100%; height: 400px; border: thin solid black" class="my-2"></div>
    <script>
        window.onload = function () {
            let container = document.getElementById("network")

            new vis.Network(container, {
                nodes: new vis.DataSet({!! json_encode($graph_nodes) !!}),
                edges: new vis.DataSet({!! json_encode($graph_edges) !!}),
            }, {
                layout: {
                    hierarchical: {
                        direction: "UD",
                    },
                },
                edges: {
                    arrows: "from",
                },
                physics: false,
            })
        }
    </script>

    <div>
        @if($users->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover">
                    <thead>
                    <tr>
                        <th> ID </th>
                        <th> Name </th>
                        <th> E-Mail </th>
                        <th> Parent </th>
                        <th> Children </th>
                        <th> Deposit </th>

                        <th> Total Anak Kiri </th>
                        <th> Total Anak Kanan </th>

                        <th> Total Deposit Kiri </th>
                        <th> Total Deposit Kanan </th>

                        <th> Controls </th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td> {{ $user->id }} </td>
                            <td> {{ $user->name  }} </td>
                            <td> {{ $user->email  }} </td>
                            <td> {{ $user->parent_ref->ancestor->name ?? "-" }} </td>
                            <td> {{ implode(", ", $user->children_refs->pluck("descendant.name")->toArray() ?? []) }} </td>
                            <td> {{ $user->deposit_amount ? number_format($user->deposit_amount) : null }} </td>

                            <td> {{ $user->left_descendant_count }} </td>
                            <td> {{ $user->right_descendant_count }} </td>
                            <td> {{ $user->left_deposit_total }} </td>
                            <td> {{ $user->right_deposit_total }} </td>


                            <td>
                                <form action="{{ route("user.destroy", $user) }}"
                                      method="POST"
                                >
                                    @csrf
                                    @method("DELETE")
                                    <button class="btn btn-danger btn-sm">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>

        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __("messages.errors.no_data") }}
            </div>
        @endif
    </div>
@endsection
