<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Middleware\SubstituteBindings;

use App\Models\User;
use App\Models\Practice;
use App\Models\Invitation;
use App\Models\Deadline;

use App\Mail\InviteUser;

use App\Http\Controllers\PracticeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\IndividualController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\CompanyCardController;
use App\Http\Controllers\CHSearchController;
use App\Http\Controllers\CompanyImportController;
use App\Http\Controllers\ClientImportController;
use App\Http\Controllers\S3DocumentController;        // S3 documents (settings + company)
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\HmrcController;               // existing
use App\Http\Controllers\Auth\PasswordResetController; // existing

/*
|--------------------------------------------------------------------------
| Root landing + dashboard
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
| Password reset
|--------------------------------------------------------------------------
*/
Route::get('/password/forgot', [PasswordResetController::class, 'showRequestForm'])
    ->name('password.request');

Route::post('/password/email', [PasswordResetController::class, 'sendLink'])
    ->name('password.email');

Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/password/reset', [PasswordResetController::class, 'handleReset'])
    ->name('password.update');

/*
|--------------------------------------------------------------------------
| CH import (global, generic)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified'])->group(function () {
    Route::post('/companies/from-ch', [CompanyImportController::class, 'store'])->name('companies.import');
    Route::post('/clients/from-ch',   [ClientImportController::class,   'store'])->name('clients.import');
});

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
| Workspace routes (practice-scoped)  →  /p/{slug}/...
|--------------------------------------------------------------------------
*/
Route::prefix('/p/{practice:slug}')
    ->middleware([
        SubstituteBindings::class,
        'auth',
        'verified',
        \App\Http\Middleware\EnsurePracticeAccess::class,
    ])
    ->group(function () {

        // Workspace home
        Route::get('/', function (Practice $practice) {
            return redirect()->route('practice.users.index', $practice->slug);
        })->name('practice.home');

        // CH Search page + proxy
        Route::get('/ch', function (Practice $practice) {
            return view('ch', ['practice' => $practice]);
        })->name('practice.ch.page');

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

        // Companies index → resources/views/companies/index.blade.php
        Route::get('/companies', function (Practice $practice) {
            $user = Auth::user();

            $companies = $user?->companies()
                ->orderBy('name')
                ->get() ?? collect();

            // pass members for dropdowns
            $members = $practice->members()->orderBy('users.name')->get();

            return view('companies.index', [
                'companies' => $companies,
                'practice'  => $practice,
                'members'   => $members,
            ]);
        })->name('practice.companies.index');

        Route::post('/companies', fn () => back()->with('status','Companies store not implemented yet.'))
            ->name('practice.companies.store');

        // Company card (modal)
        Route::get('/company-card/{companyNumber}', [CompanyCardController::class, 'show'])
            ->name('practice.company.card');

        // ---------- Assign users to roles (manager/accountant/...) ----------
        Route::post('/companies/{companyParam}/assign-user', function (Request $request, Practice $practice, string $companyParam) {
            $user = Auth::user();

            $company = \App\Models\Company::where(function ($q) use ($companyParam) {
                $slugGuess = Str::slug($companyParam);
                $q->when(is_numeric($companyParam), fn ($qq) => $qq->orWhere('id', $companyParam))
                    ->orWhere('company_number', $companyParam)
                    ->orWhereRaw('LOWER(name) = ?', [strtolower(str_replace('-', ' ', $companyParam))]);
                if (Schema::hasColumn('companies', 'slug')) $q->orWhere('slug', $slugGuess);
            })->firstOrFail();

            if (! $user->companies()->where('companies.id', $company->id)->exists()) {
                return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
            }

            $data = $request->validate([
                'field'   => ['required','string'],
                'user_id' => ['nullable','integer','exists:users,id'],
            ]);

            $map = [
                'manager'          => 'manager_id',
                'accountant'       => 'accountant_id',
                'bookkeeper'       => 'bookkeeper_id',
                'reviewer'         => 'reviewer_id',
                'payroll_prepared' => 'payroll_prepared_by_id',
            ];
            if (! isset($map[$data['field']])) {
                return response()->json(['ok' => false, 'error' => 'Invalid field'], 422);
            }

            // ensure selected user is a member of the practice (if provided)
            if (! empty($data['user_id'])) {
                $isMember = $practice->members()->where('users.id', $data['user_id'])->exists();
                if (! $isMember) return response()->json(['ok'=>false,'error'=>'User not in practice'],422);
            }

            DB::table('companies')->where('id', $company->id)->update([
                $map[$data['field']] => $data['user_id'] ?: null,
                'updated_at'         => now(),
            ]);

            return response()->json(['ok' => true]);
        })->name('practice.companies.assignUser');

        // Company details
        Route::get('/companies/{companyParam}', function (Practice $practice, string $companyParam) {
            $user = Auth::user();

            $company = \App\Models\Company::where(function ($q) use ($companyParam) {
                $slugGuess = Str::slug($companyParam);
                $q->when(is_numeric($companyParam), fn ($qq) => $qq->orWhere('id', $companyParam))
                    ->orWhere('company_number', $companyParam)
                    ->orWhereRaw('LOWER(name) = ?', [strtolower(str_replace('-', ' ', $companyParam))]);
                if (Schema::hasColumn('companies', 'slug')) {
                    $q->orWhere('slug', $slugGuess);
                }
            })->firstOrFail();

            if (! $user->companies()->where('companies.id', $company->id)->exists()) {
                abort(403, 'You do not have access to this company.');
            }

            $profile = [];
            if (!empty($company->raw_profile_json)) {
                $profile = is_array($company->raw_profile_json)
                    ? $company->raw_profile_json
                    : (json_decode($company->raw_profile_json, true) ?: []);
            }

            $company->number  = $company->company_number ?? ($profile['company_number'] ?? null);
            $createdRaw       = $company->date_of_creation ?? ($profile['date_of_creation'] ?? null);
            $company->created = $createdRaw ? \Carbon\Carbon::parse($createdRaw)->format('d/m/Y') : null;

            $addr = $company->registered_office_address ?? ($profile['registered_office_address'] ?? null);
            if (is_string($addr)) { $addr = json_decode($addr, true) ?: []; }
            $addr = is_array($addr) ? $addr : [];
            $company->address = implode(', ', array_filter([
                $addr['address_line_1'] ?? $addr['address_line1'] ?? null,
                $addr['address_line_2'] ?? $addr['address_line2'] ?? null,
                $addr['locality']       ?? $addr['town']         ?? null,
                $addr['region']         ?? null,
                $addr['postal_code']    ?? $addr['postcode']     ?? null,
                $addr['country']        ?? null,
            ]));

            $company->sic_codes = $profile['sic_codes'] ?? [];

            $accNextDue = $company->accounts_next_due
                ?? ($profile['accounts']['next_accounts']['due_on'] ?? null);
            $accNextEnd = $company->accounts_next_period_end_on
                ?? ($profile['accounts']['next_accounts']['period_end_on'] ?? null);
            $accOverdue = (bool) ($company->accounts_overdue
                ?? ($profile['accounts']['next_accounts']['overdue'] ?? false));

            $accounts = [
                'next_due'        => $accNextDue ? \Carbon\Carbon::parse($accNextDue)->format('d/m/Y') : null,
                'next_made_up_to' => $accNextEnd ? \Carbon\Carbon::parse($accNextEnd)->format('d/m/Y') : null,
                'overdue'         => $accOverdue,
                'last_accounts'   => $profile['accounts']['last_accounts'] ?? null,
            ];

            $csNextDue  = $company->confirmation_next_due
                ?? ($profile['confirmation_statement']['next_due'] ?? null);
            $csNextUpTo = $company->confirmation_next_made_up_to
                ?? ($profile['confirmation_statement']['next_made_up_to'] ?? null);
            $csOverdue  = (bool) ($company->confirmation_overdue
                ?? ($profile['confirmation_statement']['overdue'] ?? false));

            $confirmation = [
                'next_due'        => $csNextDue  ? \Carbon\Carbon::parse($csNextDue)->format('d/m/Y') : null,
                'next_made_up_to' => $csNextUpTo ? \Carbon\Carbon::parse($csNextUpTo)->format('d/m/Y') : null,
                'overdue'         => $csOverdue,
            ];

            // No OneDrive here; only S3 used in this app flow
            $onedrive = [
                'connected' => false,
                'items'     => [],
                'webUrl'    => null,
                'folderRel' => '',
            ];

            // Optional buckets to keep the view happy (if it expects arrays)
            $officersActive   = $officersActive   ?? [];
            $officersResigned = $officersResigned ?? [];
            $pscsCurrent      = $pscsCurrent      ?? [];
            $pscsFormer       = $pscsFormer       ?? [];
            $nextDeadlines    = $nextDeadlines    ?? [];
            $lateHistory      = $lateHistory      ?? [];

            return view('companies.show', [
                'practice'         => $practice,
                'company'          => $company,
                'accounts'         => $accounts,
                'confirmation'     => $confirmation,
                'officersActive'   => $officersActive,
                'officersResigned' => $officersResigned,
                'pscsCurrent'      => $pscsCurrent,
                'pscsFormer'       => $pscsFormer,
                'nextDeadlines'    => $nextDeadlines,
                'lateHistory'      => $lateHistory,
                'onedrive'         => $onedrive,
            ]);
        })->name('practice.companies.show');

        // ----- S3 SETTINGS (practice-scoped) -----
        Route::get('/settings/s3', [S3DocumentController::class, 'settings'])->name('practice.settings.s3');
        Route::post('/settings/s3', [S3DocumentController::class, 'saveSettings'])->name('practice.settings.s3.save');

        // ----- S3 COMPANY DOCUMENTS -----
        Route::get('/companies/{companyParam}/documents/s3', [S3DocumentController::class, 'showCompany'])->name('practice.companies.docs.s3');

        // Alias so existing links `route('companies.documents', [$practice, $company])` open S3
        Route::get('/companies/{companyParam}/documents', [S3DocumentController::class, 'showCompany'])->name('companies.documents');

        Route::post('/companies/{companyParam}/documents/s3/folder',   [S3DocumentController::class, 'createFolder'])->name('practice.companies.docs.s3.folder');
        Route::post('/companies/{companyParam}/documents/s3/upload',   [S3DocumentController::class, 'upload'])->name('practice.companies.docs.s3.upload');
        Route::get ('/companies/{companyParam}/documents/s3/preview/{encoded}', [S3DocumentController::class, 'preview'])->name('practice.companies.docs.s3.preview');
        Route::get ('/companies/{companyParam}/documents/s3/open/{encoded}',    [S3DocumentController::class, 'open'])->name('practice.companies.docs.s3.open');
        Route::get ('/companies/{companyParam}/documents/s3/dl/{encoded}',      [S3DocumentController::class, 'download'])->name('practice.companies.docs.s3.download');
        Route::post('/companies/{companyParam}/documents/s3/share/{encoded}',   [S3DocumentController::class, 'share'])->name('practice.companies.docs.s3.share');
        Route::delete('/companies/{companyParam}/documents/s3/rm/{encoded}',    [S3DocumentController::class, 'delete'])->name('practice.companies.docs.s3.delete');

        // Clients
        Route::get('/clients', function (Practice $practice) {
            $user = Auth::user();

            $clients = collect();
            if ($user) {
                $rows = DB::table('clients')
                    ->join('client_user', 'clients.id', '=', 'client_user.client_id')
                    ->where('client_user.user_id', $user->id)
                    ->select('clients.*')
                    ->orderByRaw("
                        COALESCE(NULLIF(TRIM(clients.name), ''),
                                 NULLIF(TRIM(clients.company_name), ''),
                                 'zzzz') ASC
                    ")
                    ->get();

                $clients = $rows->map(function ($c) {
                    $c->display = $c->name ?: $c->company_name ?: ('Client #'.$c->id);
                    return $c;
                });
            }

            return view('clients', [
                'clients'  => $clients,
                'practice' => $practice,
            ]);
        })->name('practice.clients.index');

        Route::post('/clients', fn () => back()->with('status','Clients store not implemented yet.'))
            ->name('practice.clients.store');

        Route::post('/clients/from-ch', [ClientImportController::class, 'store'])
            ->name('practice.clients.import');

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
            $user = Auth::user();

            $companyIds = $user?->companies()->select('companies.id')->pluck('id') ?? collect();
            $companies  = \App\Models\Company::whereIn('id', $companyIds)->get()->keyBy('id');

            foreach ($companies as $co) {
                if ($co->accounts_next_due && $co->accounts_next_period_end_on) {
                    Deadline::updateOrCreate(
                        [
                            'company_id'    => $co->id,
                            'type'          => 'accounts',
                            'period_end_on' => $co->accounts_next_period_end_on,
                            'due_on'        => $co->accounts_next_due,
                        ],
                        [
                            'status' => $co->accounts_overdue ? 'overdue' : 'upcoming',
                            'notes'  => 'Next accounts deadline from CH profile',
                        ]
                    );
                }

                if ($co->confirmation_next_due && $co->confirmation_next_made_up_to) {
                    Deadline::updateOrCreate(
                        [
                            'company_id'    => $co->id,
                            'type'          => 'confirmation_statement',
                            'period_end_on' => $co->confirmation_next_made_up_to,
                            'due_on'        => $co->confirmation_next_due,
                        ],
                        [
                            'status' => $co->confirmation_overdue ? 'overdue' : 'upcoming',
                            'notes'  => 'Next confirmation statement deadline from CH profile',
                        ]
                    );
                }
            }

            $rows = Deadline::whereIn('company_id', $companyIds)
                ->whereIn('type', ['accounts','confirmation_statement'])
                ->where(function($q){
                    $q->whereNull('status')->orWhereIn('status', ['upcoming','overdue']);
                })
                ->orderBy('type')
                ->orderBy('due_on')
                ->get();

            // keep only earliest per (company, type)
            $nextPerType = [];
            foreach ($rows as $d) {
                $key = $d->company_id.'|'.$d->type;
                if (!isset($nextPerType[$key])) {
                    $nextPerType[$key] = $d;
                } else {
                    $curr = $nextPerType[$key];
                    $dDue = $d->due_on ? \Carbon\Carbon::parse($d->due_on) : null;
                    $cDue = $curr->due_on ? \Carbon\Carbon::parse($curr->due_on) : null;
                    if ($dDue && $cDue && $dDue->lt($cDue)) {
                        $nextPerType[$key] = $d;
                    }
                }
            }
            $rows = collect(array_values($nextPerType));

            $prettyType = function (string $type): string {
                return $type === 'accounts'
                    ? 'Accounts'
                    : ($type === 'confirmation_statement' ? 'Confirmation statement'
                        : ucwords(str_replace('_', ' ', $type)));
            };
            $fmtDue = function (?string $dueStr): string {
                if (!$dueStr) return '—';
                $due   = \Carbon\Carbon::parse($dueStr)->startOfDay();
                $today = now()->startOfDay();
                $diff  = $today->diffInDays($due, false);
                $label = $due->format('d/m/Y');
                if     ($diff > 0)  { $label .= ' (in '.$diff.' days)'; }
                elseif ($diff === 0){ $label .= ' (today)'; }
                else                { $label .= ' ('.abs($diff).' days late)'; }
                return $label;
            };

            $accounts = [];
            $confirmations = [];
            $combined = [];

            foreach ($rows as $d) {
                $co = $companies->get($d->company_id);
                $d->title       = ($co?->name) ? ($prettyType($d->type).' — '.$co->name) : $prettyType($d->type);
                $d->year_end    = $d->period_end_on ? \Carbon\Carbon::parse($d->period_end_on)->format('d/m/Y') : '—';
                $d->display_due = $fmtDue($d->due_on);

                $combined[] = $d;
                if ($d->type === 'accounts') {
                    $accounts[] = $d;
                } elseif ($d->type === 'confirmation_statement') {
                    $confirmations[] = $d;
                }
            }

            return view('deadlines', [
                'deadlines'     => collect($combined),
                'auto'          => collect($combined),
                'manual'        => collect(),
                'accounts'      => $accounts,
                'confirmations' => $confirmations,
                'practice'      => $practice,
            ]);
        })->name('practice.deadlines.index');

        Route::post('/deadlines', fn () => back()->with('status','Deadlines store not implemented yet.'))
            ->name('practice.deadlines.store');

        Route::delete('/deadlines/{id}', fn (string $id) => back()->with('status',"Deadline {$id} deleted (stub)."))
            ->name('practice.deadlines.destroy');

        Route::match(['GET','POST'], '/deadlines/refresh-all', fn () => back()->with('status','Deadlines refresh queued (stub).'))
            ->name('practice.deadlines.refreshAll');

        // HMRC VAT routes
        Route::get('/companies/{company}/hmrc/connect', [HmrcController::class, 'connectForCompany'])->name('practice.hmrc.connect');
        Route::get('/companies/{company}/hmrc/obligations', [HmrcController::class, 'obligationsForCompany'])->name('practice.hmrc.obligations');
    });

/*
|--------------------------------------------------------------------------
| HMRC OAuth callback (existing)
|--------------------------------------------------------------------------
*/
Route::get('/hmrc/callback', [HmrcController::class, 'callback'])
    ->middleware(['auth'])
    ->name('hmrc.callback');

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
});
