<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Path;
use App\Models\Referral;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|View
     */
    public function showRegistrationForm(Request $request)
    {
        $referred_user_email = $request->query("ref");

        $user = User::query()
            ->where("email", $referred_user_email)
            ->first();

        return view('auth.register', [
            "referred_user" => $user,
        ]);
    }

    public function register(Request $request)
    {
        $data = $this->validator($request->all())->validate();

        // Cek user sumber link referral
        /** @var User $referred_user */
        $referred_user = User::query()
            ->where("email", $request->query("ref"))
            ->first();

        DB::beginTransaction();

        event(new Registered($user = $this->create($data)));

        if ($referred_user !== null) {
            Referral::query()->create([
                "referral_source_id" => $referred_user->id,
                "user_id" => $user->id,
            ]);
        }

        /** @var User $root_user */
        $root_user = User::query()
            ->where("level", User::LEVEL_REGULAR)
            ->where("is_root", 1)
            ->first();

        if ($root_user === null) {
            // Daftarkan user sebagai root
            Path::query()->create([
                "ancestor_id" => $user->id,
                "descendant_id" => $user->id,
                "tree_depth" => 0,
            ]);
        } else {
            // Cari user yang kakinya masih kosong dan daftarkan user baru sebagai salah satu kakinya
            $parent = ($referred_user ?? $root_user)->nextEligibleDescendant();

            User::attachDirectly(
                $parent->id,
                $user->id,
            );
        }

        DB::commit();

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User|Builder|Model
     */
    protected function create(array $data)
    {
        // Cek apakah sudah terdapat user biasa yang sudah terletak dalam tree
        /** @var User $root_user */
        $root_user = User::query()
            ->where("level", User::LEVEL_REGULAR)
            ->where("is_root", 1)
            ->first();

        DB::beginTransaction();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'level' => User::LEVEL_REGULAR,
            'is_root' => $root_user ? 0 : 1
        ]);

        DB::commit();

        return $user;
    }

    public function redirectPath()
    {
        return RouteServiceProvider::home();
    }
}
