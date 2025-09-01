<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Middleware\SubstituteBindings;

use App\Models\User;
use App\Models\Practice;
use App\Models\Invitation;

use App\Mail\InviteUser;

use App\Http\Controllers\PracticeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\IndividualController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\CompanyCardController;
use App\Http\Controllers\CHSearchController;   // CH JSON proxy
use App\Http\Controllers\CompanyImportController; // <- Add company from CH (creates deadlines)
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
    $practice = Practice::where('owner_id', $user->id)->latest('id')->first()
        ?: Practice::whereHas('members', fn($q) => $q->where('users.id', $user->id))->latest('id')->first();

    if (! $user->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    // Redirect to that user's workspace URL
    return $practice
        ? redirect()->route('practice.users.index', $practice->slug)->with('status', 'Welcome back!')
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
    $practice = Practice::where('owner_id', $user->id)->latest('id')->first()
        ?: Practice::whereHas('members', fn($q) => $q->where('users.id', $user->id))->latest('id')->first();

    return $practice
        ? redirect()->route('practice.users.index', $practice->slug)->with('status', 'Email verified!')
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
| Practice create/store (one practice per owner)
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
| Global CH import endpoint (non-workspace path; used by generic JS)
|--------------------------------------------------------------------------
|
| This duplicates the practice-scoped route below so calls to
| /companies/from-ch also work if the current page isn’t under /p/{slug}.
|
*/
Route::middleware(['auth','verified'])->group(function () {
    Route::post('/companies/from-ch', [CompanyImportController::class, 'store'])
        ->name('companies.import');
});

/*
|--------------------------------------------------------------------------
| Workspace routes (practice-scoped)  →  /p/{slug}/...
|--------------------------------------------------------------------------
|
| Use closures (not Route::view) so we pass the Practice model to views.
| Route::view would merge route params into view data and can overwrite
| $practice with the slug string.
|
*/
Route::prefix('/p/{practice:slug}')
    ->middleware([
        SubstituteBindings::class,                        // ensure {practice} is bound first
        'auth',
        'verified',
        \App\Http\Middleware\EnsurePracticeAccess::class, // workspace access gate
    ])
    ->group(function () {

        // Workspace home
        Route::get('/', function (Practice $practice) {
            return redirect()->route('practice.users.index', $practice->slug);
        })->name('practice.home');

        // CH Search page
        Route::get('/ch', function (Practice $practice) {
            return view('ch', ['practice' => $practice]);
        })->name('practice.ch.page');

        // CH Search JSON proxy (keeps API key server-side)
        Route::get('/ch/search', [CHSearchController::class, 'search'])
            ->name('practice.ch.search');

        // Users
        Route::get('/users', function (Practice $practice) {
            $members = $practice->members()->orderBy('users.name')->get();

            $invites = collect();
            if (Schema::hasTable('invitations')) {
                $invites = Invitation::where('practice_id', $practice->id)->latest('id')->get();
            }

            return view('users.index', compact('practice','members','invites'));
        })->name('practice.users.index');

        // Invite user (send email with token)
        Route::post('/users', function (Request $request, Practice $practice) {
            Log::info('Invite POST hit', ['by' => Auth::id(), 'practice' => $practice->id]);

            $data = $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'surname'    => ['required', 'string', 'max:255'],
                'email'      => ['required', 'email', 'max:255'],
            ]);

            if ($existing = User::where('email', $data['email'])->first()) {
                $practice->members()->syncWithoutDetaching([$existing->id => ['role' => 'member']]);
                return redirect()->route('practice.users.index', $practice->slug)
                    ->with('status', 'User already exists — added to this practice.');
            }

            try {
                $inv = Invitation::create([
                    'practice_id' => $practice->id,
                    'email'       => $data['email'],
                    'first_name'  => $data['first_name'],
                    'surname'     => $data['surname'],
                    'role'        => 'member',
                    'token'       => Str::random(64),
                    'expires_at'  => now()->addDays(7),
                ]);

                Mail::to($inv->email)->send(new InviteUser($inv));

                return redirect()->route('practice.users.index', $practice->slug)
                    ->with('status', 'Invitation sent to '.$inv->email.'.')
                    ->with('invite_url', route('invites.show', $inv->token));

            } catch (\Throwable $e) {
                Log::error('Invite failure', ['error' => $e->getMessage()]);
                $fallbackUrl = isset($inv) ? route('invites.show', $inv->token) : null;

                return redirect()->route('practice.users.index', $practice->slug)
                    ->withErrors(['invite' => 'Could not send the email: '.$e->getMessage()])
                    ->with('invite_url', $fallbackUrl);
            }
        })->name('practice.users.store');

        // Remove a member (protect last admin)
        Route::delete('/users/{user}', function (Practice $practice, User $user) {
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
        })->name('practice.users.destroy');

        // Companies
        Route::get('/companies', function (Practice $practice) {
            return view('companies', [
                'companies' => [],
                'practice'  => $practice,
            ]);
        })->name('practice.companies.index');

        Route::post('/companies', fn () => back()->with('status','Companies store not implemented yet.'))
            ->name('practice.companies.store');

        // Add company from Companies House (AJAX from CH modal) — also creates deadlines
        Route::post('/companies/from-ch', [CompanyImportController::class, 'store'])
            ->name('practice.companies.import');

        // Company card (HTML for modal)
        Route::get('/company-card/{companyNumber}', [CompanyCardController::class, 'show'])
            ->name('practice.company.card');

        // Clients
        Route::get('/clients', function (Practice $practice) {
            return view('clients', [
                'clients'   => [],
                'practice'  => $practice,
            ]);
        })->name('practice.clients.index');

        Route::post('/clients', fn () => back()->with('status','Clients store not implemented yet.'))
            ->name('practice.clients.store');

        // Tasks
        if (class_exists(TaskController::class)) {
            Route::get('/tasks', [TaskController::class, 'index'])->name('practice.tasks.index');
            Route::post('/tasks', [TaskController::class, 'store'])->name('practice.tasks.store');
            Route::get('/tasks/create', [TaskController::class, 'create'])->name('practice.tasks.create');
            Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('practice.tasks.show');
            Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('practice.tasks.update');
            Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('practice.tasks.destroy');
            Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('practice.tasks.edit');
        } else {
            Route::get('/tasks', function (Practice $practice) {
                return view('tasks.index', ['practice' => $practice, 'tasks' => collect()]);
            })->name('practice.tasks.index');
        }

        // Deadlines
        Route::get('/deadlines', function (Practice $practice) {
            return view('deadlines', [
                'deadlines' => [],
                'auto'      => [],
                'manual'    => [],
                'practice'  => $practice,
            ]);
        })->name('practice.deadlines.index');

        Route::post('/deadlines', fn () => back()->with('status','Deadlines store not implemented yet.'))
            ->name('practice.deadlines.store');

        Route::delete('/deadlines/{id}', fn (string $id) => back()->with('status',"Deadline {$id} deleted (stub)."))
            ->name('practice.deadlines.destroy');

        Route::match(['GET','POST'], '/deadlines/refresh-all', fn () => back()->with('status','Deadlines refresh queued (stub).'))
            ->name('practice.deadlines.refreshAll');
    });

/*
|--------------------------------------------------------------------------
| Fallback redirects from old flat URLs → last/active practice
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified'])->group(function () {
    $toActive = function (string $name, array $params = []) {
        $uid = Auth::id();
        $practice = Practice::where('owner_id', $uid)->latest('id')->first()
            ?: Practice::whereHas('members', fn($q) => $q->where('users.id', $uid))->latest('id')->first();

        return $practice
            ? redirect()->route($name, array_merge(['practice' => $practice->slug], $params))
            : redirect()->route('practices.create')->with('status','Create your practice first.');
    };

    Route::get('/users',     fn () => $toActive('practice.users.index'))->name('users.index');
    Route::get('/companies', fn () => $toActive('practice.companies.index'))->name('companies.index');
    Route::get('/clients',   fn () => $toActive('practice.clients.index'))->name('clients.index');
    Route::get('/tasks',     fn () => $toActive('practice.tasks.index'))->name('tasks.index');
    Route::get('/deadlines', fn () => $toActive('practice.deadlines.index'))->name('deadlines.index');

    // If you need old flat names for specific subroutes, add opt-in aliases here.
    // (Avoid duplicating 'practice.company.card' outside the workspace.)
});
