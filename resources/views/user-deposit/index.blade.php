@extends("layouts.app")

@section("content")
    <h1> Your Deposits </h1>

    <div class="card mb-3">
        <div class="card-header">
            Add Deposit
        </div>

        <div class="card-body">
            <form action="{{ route("user.deposit.store", $user) }}"
                  method="POST"
            >
                @csrf
                @method("POST")

                <div class="form-group">
                    <label for="amount"> Amount: </label>
                    <input
                            id="amount"
                            type="text"
                            placeholder="Amount"
                            class="form-control @error("amount") is-invalid @enderror"
                            name="amount"
                            value="{{ old("amount") }}"
                    />
                    @error("amount")
                    <span class="invalid-feedback">
                    {{ $message }}
                </span>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary">
                        Add
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div>
        @if($deposits->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover">
                    <thead>
                        <tr>
                            <th> # </th>
                            <th> Amount </th>
                            <th> Type </th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach ($deposits as $deposit)
                        <tr>
                            <td> {{ $deposits->firstItem() + $loop->index }} </td>
                            <td> {{ $deposit->amount }} </td>
                            <td> {{ $deposit->type }} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $deposits->links() }}
            </div>

        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __("messages.errors.no_data") }}
            </div>
        @endif
    </div>
@endsection
