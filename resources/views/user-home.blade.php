@extends("layouts.app")

@section("content")
    <h1> Home </h1>

    @if($user->deposit_amount === null)
        <div class="card">
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
        <div class="alert alert-success">
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
@endsection
