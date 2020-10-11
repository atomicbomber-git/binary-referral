@extends("layouts.app")

@section("content")
    <div id="network" style="width: 100%; height: 600px"></div>
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
            })
        }
    </script>

    <div>
        @if($users->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover">
                    <thead>
                    <tr>
                        <th> # </th>
                        <th> Name </th>
                        <th> E-Mail </th>
                        <th> Referred By (Parent) </th>
                        <th> Referrals (Children) </th>
                        <th> Controls </th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td> {{ $users->firstItem() + $loop->index }} </td>
                            <td> {{ $user->name  }} </td>
                            <td> {{ $user->email  }} </td>
                            <td> {{ $user->parent_ref->parent_user->name ?? "-" }} </td>
                            <td> {{ implode(", ", $user->children_refs->pluck("child_user.name")->toArray() ?? []) }} </td>
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
