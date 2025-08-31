<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;   // ⬅️ add this

use App\Models\User;
use App\Models\Practice;
use App\Models\Invitation;

use App\Mail\InviteUser;

use App\Http\Controllers\PracticeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\IndividualController;
use App\Http\Controllers\InviteController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Landing (canvas top page)
|--------------------------------------------------------------------------
*/
Route::view('/', 'setup.landing')->name('landing');

/*
|--------------------------------------------------------------------------
| Optionally include extra route files if present
|--------------------------------------------------------------------------
*/
foreach (['landing.php', 'auth_manual.php', 'demo.php'] as $extra) {
    $path = base_path('routes/'.$extra);
    if (file_exists($path)) require $path;
}

/*
|--------------------------------------------------------------------------
| Registration + Login (owners who self-register)
|--------------------------------------------------------------------------
*/
Route::view('/register', 'auth.register')->name('register');

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name'     => ['required', 'string', 'max:255'],
        'surname'  => ['required', 'string', 'max:255'],
        'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:6', 'confirmed'],
    ]);

    $user = User::create([
        'name'     => trim($data['name'].' '.$data['surname']),
        'email'    => $data['email'],
        'password' => Hash::make($data['password']),
    ]);

    Auth::login($user);
    $user->sendEmailVerificationNotification();

    return redirect()->route('verification.notice')
        ->with('status', 'We’ve emailed you a verification link.');
})->name('register.post');

Route::view('/login', 'auth.login')->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials, true)) {
        return back()->withErrors(['email' => 'Invalid email or password'])->onlyInput('email');
    }

    $request->session()->regenerate();

    $user = $request->user();
    $hasPractice = Practice::where('owner_id', $user->id)->exists();

    if (! $user->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    return $hasPractice
        ? redirect()->route('users.index')->with('status', 'Welcome back!')
        : redirect()->route('practices.create')->with('status', 'Let’s create your practice.');
})->name('login.post');

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('landing')->with('status', 'Logged out.');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Email verification (used for self-registered owners)
|--------------------------------------------------------------------------
*/
Route::get('/email/verify', fn () => view('auth.verify-email'))
    ->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    $user = $request->user();
    $hasPractice = Practice::where('owner_id', $user->id)->exists();

    return $hasPractice
        ? redirect()->route('users.index')->with('status', 'Email verified!')
        : redirect()->route('practices.create')->with('status', 'Email verified — create your practice.');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'Verification link sent.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Account (profile) – requires login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/account', fn () => view('account', ['user' => auth()->user()]))->name('account');

    Route::patch('/account', function (Request $request) {
        $user = $request->user();

        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email,'.$user->id],
            'password' => ['nullable','string','min:6','confirmed'],
        ]);

        $user->name = $data['name'];

        if ($user->email !== $data['email']) {
            $user->email = $data['email'];
            $user->email_verified_at = null;
            $user->save();
            $user->sendEmailVerificationNotification();
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('status', 'Account updated.');
    })->name('account.update');

    Route::delete('/account', function (Request $request) {
        $user = $request->user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('landing')->with('status', 'Account deleted.');
    })->name('account.destroy');
});

/*
|--------------------------------------------------------------------------
| Practice create/store ONLY (one practice per owner)
|--------------------------------------------------------------------------
*/
Route::get('/practices/create', [PracticeController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('practices.create');

Route::post('/practices', [PracticeController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('practices.store');

/*
|--------------------------------------------------------------------------
| Public invitation endpoints (no auth)
|--------------------------------------------------------------------------
*/
Route::get('/invites/{token}', [InviteController::class, 'show'])->name('invites.show');
Route::post('/invites/{token}', [InviteController::class, 'accept'])->name('invites.accept');

/*
|--------------------------------------------------------------------------
| App pages with original global route names (menu targets)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified'])->group(function () {

    // CH Search
    Route::view('/ch', 'ch')->name('ch.page');

    // Users (members of your single practice)
    Route::get('/users', function () {
        $uid = Auth::id();
        $practice = Practice::where('owner_id', $uid)->latest('id')->first()
            ?: Practice::whereHas('members', fn($q) => $q->where('users.id', $uid))->latest('id')->first();

        if (! $practice) {
            return redirect()->route('practices.create')->with('status','Create your practice first.');
        }

        $members = $practice->members()->orderBy('users.name')->get();

        // ✅ Guarded: if the 'invitations' table isn't migrated yet, avoid crash
        $invites = collect();
        if (Schema::hasTable('invitations')) {
            $invites = Invitation::where('practice_id', $practice->id)->latest('id')->get();
        }

        return view('users.index', compact('practice','members','invites'));
    })->name('users.index');

    // Invite user (send email with token)
    Route::post('/users', function (Request $request) {
        Log::info('Invite POST hit', ['by' => Auth::id(), 'url' => $request->fullUrl()]);

        $uid = Auth::id();
        $practice = Practice::where('owner_id', $uid)->latest('id')->first()
            ?: Practice::whereHas('members', fn($q) => $q->where('users.id', $uid))->latest('id')->first();

        if (! $practice) {
            return redirect()->route('users.index')
                ->withErrors(['practice' => 'Create your practice first.']);
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'surname'    => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
        ]);

        // If the user already exists, just attach immediately
        if ($existing = User::where('email', $data['email'])->first()) {
            $practice->members()->syncWithoutDetaching([$existing->id => ['role' => 'member']]);
            return redirect()->route('users.index')
                ->with('status', 'User already exists — added to this practice.')
                ->with('invite_url', null);
        }

        try {
            // Create invitation record
            $inv = Invitation::create([
                'practice_id' => $practice->id,
                'email'       => $data['email'],
                'first_name'  => $data['first_name'],
                'surname'     => $data['surname'],
                'role'        => 'member',
                'token'       => Str::random(64),
                'expires_at'  => now()->addDays(7),
            ]);

            // Send email
            Mail::to($inv->email)->send(new InviteUser($inv));
            Log::info('Invite email dispatched', ['to' => $inv->email]);

            return redirect()->route('users.index')
                ->with('status', 'Invitation sent to '.$inv->email.'.')
                ->with('invite_url', route('invites.show', $inv->token));

        } catch (\Throwable $e) {
            Log::error('Invite failure', ['error' => $e->getMessage()]);
            $fallbackUrl = isset($inv) ? route('invites.show', $inv->token) : null;

            return redirect()->route('users.index')
                ->withErrors(['invite' => 'Could not send the email: '.$e->getMessage()])
                ->with('invite_url', $fallbackUrl);
        }
    })->name('users.store');

    // Remove a member (protect last admin)
    Route::delete('/users/{user}', function (User $user) {
        $uid = Auth::id();
        $practice = Practice::where('owner_id', $uid)->latest('id')->first()
            ?: Practice::whereHas('members', fn($q) => $q->where('users.id', $uid))->latest('id')->first();

        if (! $practice) {
            return back()->withErrors(['No active practice found.']);
        }

        $adminCount = $practice->members()->wherePivot('role', 'admin')->count();
        $isAdmin = $practice->members()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'admin')
            ->exists();

        if ($isAdmin && $adminCount <= 1) {
            return back()->withErrors(['Cannot remove the last admin from this practice.']);
        }

        $practice->members()->detach($user->id);

        return back()->with('status', "User removed from {$practice->name}.");
    })->name('users.destroy');

    // Companies / Clients / Tasks / Deadlines
    Route::view('/companies', 'companies', ['companies' => []])->name('companies.index');
    Route::post('/companies', fn () => back()->with('status','Companies store not implemented yet.'))->name('companies.store');

    Route::view('/clients', 'clients', ['clients' => []])->name('clients.index');
    Route::post('/clients', fn () => back()->with('status','Clients store not implemented yet.'))->name('clients.store');

    if (class_exists(TaskController::class)) {
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    } else {
        Route::view('/tasks', 'tasks')->name('tasks.index');
    }

    Route::view('/deadlines', 'deadlines', [
        'deadlines' => [], 'auto' => [], 'manual' => [],
    ])->name('deadlines.index');

    Route::post('/deadlines', fn () => back()->with('status','Deadlines store not implemented yet.'))->name('deadlines.store');
    Route::delete('/deadlines/{id}', fn (string $id) => back()->with('status',"Deadline {$id} deleted (stub)."))->name('deadlines.destroy');
    Route::match(['GET','POST'], '/deadlines/refresh-all', fn () => back()->with('status','Deadlines refresh queued (stub).'))->name('deadlines.refreshAll');
});

/*
|--------------------------------------------------------------------------
| Individuals (unchanged stub)
|--------------------------------------------------------------------------
*/
if (class_exists(IndividualController::class)) {
    Route::get('/individuals', [IndividualController::class, 'index'])->name('individuals.index');
} else {
    Route::view('/individuals', 'individuals.index', ['individuals' => collect()])
        ->name('individuals.index');
}
