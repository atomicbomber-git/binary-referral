@extends("layouts.app")

@section("content")
    <h1> Home </h1>

    @if($user->deposit_amount === null)
        <div class="card my-2">
            <div class="card-body">
                <div class="alert alert-warning">
                    Anda belum melakukan deposit, silahkan pilih tipe deposit Anda disini:
                </div>

                <form action="{{ route("user.deposit.store", $user) }}"
                      method="POST"
                >
                    @csrf
                    @method("POST")

                    <div class="form-group">
                        <label for="deposit_type"> Deposit Type: </label>
                        <select
                                id="deposit_type"
                                type="text"
                                class="form-control @error("deposit_type") is-invalid @enderror"
                                name="deposit_type"
                        >
                            @foreach ($deposit_types as $type => $amount)
                                <option value="{{ $type }}">
                                    {{ $type }} ({{ $amount }})
                                </option>
                            @endforeach
                        </select>
                        @error("deposit_type")
                            <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary">
                            Buat Deposit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($user->deposit_amount !== null)
        <div class="alert alert-success my-2">
            <p>
                Anda telah melakukan deposit dengan besaran <strong>{{ number_format($user->deposit_amount) }}</strong>.
            </p>

            <p>
                Link referral Anda adalah
                <a class="font-weight-bold" href="{{ route("register", ["ref" => $user->email]) }}">
                    {{ route("register", ["ref" => $user->email]) }}
                </a>
            </p>
        </div>
    @endif

    <h2> Daftar Bonus </h2>

    <div>
        @if($bonuses->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover">
                    <thead>
                        <tr>
                            <th> # </th>
                            <th> Tipe </th>
                            <th> Jumlah  </th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach ($bonuses as $bonus)
                        <tr>
                            <td> {{ $bonuses->firstItem() + $loop->index }} </td>
                            <td> {{ $bonus->type }} </td>
                            <td> {{ $bonus->amount }} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $bonuses->links() }}
            </div>

        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __("messages.errors.no_data") }}
            </div>
        @endif
    </div>


@endsection
