<?php

namespace App\Http\Controllers;

use App\Mail\LeadConfirmMail;
use App\Mail\LeadUserInviteMail;
use App\Models\Lead;
use App\Models\LeadUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    /* =======================
     * 1) OPENING PAGE (FORM)
     * ======================= */
    public function showStart()
    {
        return view('landing.get-started');
    }

    /* Handle form submit: create/update the Lead and send confirmation email */
    public function start(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name'  => ['required','string','max:100'],
            'email'      => ['required','email','max:190'],
            'phone'      => ['nullable','string','max:40'],
            'practice'   => ['required','string','max:190'],
        ]);

        $lead = Lead::firstOrNew(['email' => $data['email']]);
        $lead->fill($data);

        // Ensure a URL-safe practice slug + a home path like /practice/<slug>
        if (empty($lead->practice_slug)) {
            $lead->practice_slug = $this->uniqueSlug($lead->practice, $lead->id ?? null);
        }
        if (empty($lead->home_path)) {
            $lead->home_path = '/practice/'.$lead->practice_slug;
        }

        $lead->token = $lead->token ?: Str::random(40);
        $lead->save();

        // Send confirmation email (non-blocking: errors are logged)
        try {
            Mail::to($lead->email)->send(new LeadConfirmMail($lead));
        } catch (\Throwable $e) {
            Log::error('Lead confirm mail failed: '.$e->getMessage());
        }

        // If you have a "thanks" page, keep this. Otherwise you can redirect to confirm or add-users.
        return view('landing.thanks', ['lead' => $lead]);
    }

    /* =======================
     * 2) CONFIRMATION + ADD USERS
     * ======================= */
    public function confirm(string $token)
    {
        $lead = Lead::where('token', $token)->firstOrFail();

        if (!$lead->confirmed_at) {
            $lead->confirmed_at = now();
            $lead->save();
        }

        return redirect()->route('lead.users.show', ['t' => $lead->token]);
    }

    public function showAddUsers(Request $request)
    {
        $token = $request->query('t');
        $lead  = Lead::where('token', $token)->firstOrFail();

        if (!$lead->confirmed_at) {
            return redirect()->route('lead.confirm', ['token' => $token]);
        }

        // Keep practice context in session (used by layout / redirects)
        session([
            'practice_name'  => $lead->practice,
            'practice_slug'  => $lead->practice_slug,
            'practice_token' => $lead->token,
        ]);

        $users = $lead->users()->latest()->get();

        return view('landing.add-users', [
            'lead'  => $lead,
            'users' => $users,
        ]);
    }

    public function storeUsers(Request $request)
    {
        $token = $request->input('t');
        $lead  = Lead::where('token', $token)->firstOrFail();

        // Backfill slug/home_path if missing (safety for older data)
        if (empty($lead->practice_slug)) {
            $lead->practice_slug = $this->uniqueSlug($lead->practice, $lead->id);
        }
        if (empty($lead->home_path)) {
            $lead->home_path = '/practice/'.$lead->practice_slug;
        }
        $lead->save();

        $validated = $request->validate([
            'users'                 => ['required','array','min:1'],
            'users.*.first_name'    => ['required','string','max:100'],
            'users.*.last_name'     => ['required','string','max:100'],
            'users.*.email'         => ['required','email','max:190'],
        ]);

        foreach ($validated['users'] as $u) {
            $invite = LeadUser::create([
                'lead_id'    => $lead->id,
                'first_name' => $u['first_name'],
                'last_name'  => $u['last_name'],
                'email'      => $u['email'],
                'token'      => Str::random(40),
                'invited_at' => now(),
            ]);

            // Send invite email (non-blocking: errors logged)
            try {
                Mail::to($invite->email)->send(new LeadUserInviteMail($lead, $invite));
            } catch (\Throwable $e) {
                Log::error('Invite mail failed: '.$e->getMessage());
            }
        }

        // Keep practice context
        session([
            'practice_name'  => $lead->practice,
            'practice_slug'  => $lead->practice_slug,
            'practice_token' => $lead->token,
        ]);

        // Redirect to the practice home (new empty workspace)
        return redirect($lead->home_path)->with('ok', 'Invitations sent.');
    }

    public function acceptInvite(string $token)
    {
        $user = LeadUser::where('token', $token)->firstOrFail();

        if (!$user->accepted_at) {
            $user->accepted_at = now();
            $user->save();
        }

        $lead = $user->lead()->first();

        if ($lead) {
            if (empty($lead->practice_slug)) {
                $lead->practice_slug = $this->uniqueSlug($lead->practice, $lead->id);
            }
            if (empty($lead->home_path)) {
                $lead->home_path = '/practice/'.$lead->practice_slug;
            }
            $lead->save();

            session([
                'practice_name'  => $lead->practice,
                'practice_slug'  => $lead->practice_slug,
                'practice_token' => $lead->token,
            ]);

            return redirect($lead->home_path)->with('ok', 'Invitation accepted. Welcome!');
        }

        return redirect('/')->with('ok', 'Invitation accepted.');
    }

    /* =======================
     * 3) PRACTICE PAGES
     * ======================= */
    public function practiceHome(string $slug)
    {
        $lead = Lead::where('practice_slug', $slug)->firstOrFail();

        session([
            'practice_name'  => $lead->practice,
            'practice_slug'  => $lead->practice_slug,
            'practice_token' => $lead->token,
        ]);

        return view('practice.home', ['lead' => $lead]);
    }

    public function practiceCompanies(string $slug)
    {
        $lead = Lead::where('practice_slug', $slug)->firstOrFail();

        session([
            'practice_name'  => $lead->practice,
            'practice_slug'  => $lead->practice_slug,
            'practice_token' => $lead->token,
        ]);

        // If you don't have this view, you can point to your old list or keep an empty page.
        return view('practice.companies', ['lead' => $lead]);
    }

    public function practiceCh(string $slug)
    {
        $lead = Lead::where('practice_slug', $slug)->firstOrFail();

        session([
            'practice_name'  => $lead->practice,
            'practice_slug'  => $lead->practice_slug,
            'practice_token' => $lead->token,
        ]);

        // Reuse your existing CH search view
        return view('ch', ['lead' => $lead]);
    }

    /* =======================
     * 4) MISC
     * ======================= */
    public function resend(Request $request)
    {
        $data = $request->validate(['email' => ['required','email']]);

        $lead = Lead::where('email', $data['email'])->first();
        if (!$lead) {
            return back()->with('err', 'No starter form found for that email. Please fill it again.');
        }

        if (empty($lead->practice_slug)) {
            $lead->practice_slug = $this->uniqueSlug($lead->practice, $lead->id);
        }
        if (empty($lead->home_path)) {
            $lead->home_path = '/practice/'.$lead->practice_slug;
        }
        $lead->token = $lead->token ?: Str::random(40);
        $lead->save();

        try {
            Mail::to($lead->email)->send(new LeadConfirmMail($lead));
        } catch (\Throwable $e) {
            Log::error('Lead confirm mail failed: '.$e->getMessage());
        }

        return back()->with('ok', 'Confirmation email re-sent.');
    }

    /* Generate a unique slug for the practice name (excluding an optional current lead id) */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base ?: 'practice';
        $i = 1;

        while (
            Lead::when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where('practice_slug', $slug)
                ->exists()
        ) {
            $i++;
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
