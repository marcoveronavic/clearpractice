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
    public function showStart()
    {
        return view('landing.get-started');
    }

    // Create/update a Lead and email a confirmation link
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

        if (empty($lead->practice_slug)) $lead->practice_slug = $this->uniqueSlug($lead->practice);
        if (empty($lead->home_path))     $lead->home_path     = '/practice/'.$lead->practice_slug;

        $lead->token = $lead->token ?: Str::random(40);
        $lead->save();

        try { Mail::to($lead->email)->send(new LeadConfirmMail($lead)); }
        catch (\Throwable $e) { Log::error('Lead confirm mail failed: '.$e->getMessage()); }

        return view('landing.thanks', ['lead' => $lead]);
    }

    public function confirm(string $token)
    {
        $lead = Lead::where('token', $token)->firstOrFail();
        if (!$lead->confirmed_at) { $lead->confirmed_at = now(); $lead->save(); }
        return redirect()->route('lead.users.show', ['t' => $lead->token]);
    }

    public function showAddUsers(Request $request)
    {
        $lead = Lead::where('token', $request->query('t'))->firstOrFail();
        if (!$lead->confirmed_at) return redirect()->route('lead.confirm', ['token' => $lead->token]);

        session(['practice_name'=>$lead->practice,'practice_slug'=>$lead->practice_slug,'practice_token'=>$lead->token]);
        $users = $lead->users()->latest()->get();
        return view('landing.add-users', ['lead'=>$lead, 'users'=>$users]);
    }

    public function storeUsers(Request $request)
    {
        $lead = Lead::where('token', $request->input('t'))->firstOrFail();

        if (empty($lead->practice_slug)) $lead->practice_slug = $this->uniqueSlug($lead->practice);
        if (empty($lead->home_path))     $lead->home_path     = '/practice/'.$lead->practice_slug;
        $lead->save();

        $validated = $request->validate([
            'users' => ['required','array','min:1'],
            'users.*.first_name' => ['required','string','max:100'],
            'users.*.last_name'  => ['required','string','max:100'],
            'users.*.email'      => ['required','email','max:190'],
        ]);

        foreach ($validated['users'] as $u) {
            $invite = LeadUser::create([
                'lead_id'=>$lead->id,'first_name'=>$u['first_name'],'last_name'=>$u['last_name'],
                'email'=>$u['email'],'token'=>Str::random(40),'invited_at'=>now(),
            ]);
            try { Mail::to($invite->email)->send(new LeadUserInviteMail($lead, $invite)); }
            catch (\Throwable $e) { Log::error('Invite mail failed: '.$e->getMessage()); }
        }

        session(['practice_name'=>$lead->practice,'practice_slug'=>$lead->practice_slug,'practice_token'=>$lead->token]);
        return redirect($lead->home_path)->with('ok','Invitations sent.');
    }

    public function acceptInvite(string $token)
    {
        $user = LeadUser::where('token', $token)->firstOrFail();
        if (!$user->accepted_at) { $user->accepted_at = now(); $user->save(); }

        $lead = $user->lead()->first();
        if ($lead) {
            if (empty($lead->practice_slug)) $lead->practice_slug = $this->uniqueSlug($lead->practice);
            if (empty($lead->home_path))     $lead->home_path     = '/practice/'.$lead->practice_slug;
            $lead->save();

            session(['practice_name'=>$lead->practice,'practice_slug'=>$lead->practice_slug,'practice_token'=>$lead->token]);
            return redirect($lead->home_path)->with('ok','Invitation accepted. Welcome!');
        }
        return redirect('/')->with('ok','Invitation accepted.');
    }

    public function practiceHome(string $slug)
    {
        $lead = Lead::where('practice_slug',$slug)->firstOrFail();
        session(['practice_name'=>$lead->practice,'practice_slug'=>$lead->practice_slug,'practice_token'=>$lead->token]);
        return view('practice.home',['lead'=>$lead]);
    }

    public function practiceCompanies(string $slug)
    {
        $lead = Lead::where('practice_slug',$slug)->firstOrFail();
        session(['practice_name'=>$lead->practice,'practice_slug'=>$lead->practice_slug,'practice_token'=>$lead->token]);
        return view('practice.companies',['lead'=>$lead]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base ?: 'practice';
        $i = 1;
        while (Lead::where('practice_slug',$slug)->exists()) { $i++; $slug = $base.'-'.$i; }
        return $slug;
    }

    public function resend(Request $request)
    {
        $lead = Lead::where('email', $request->validate(['email'=>['required','email']])['email'])->first();
        if (!$lead) return back()->with('err','No starter form found for that email.');

        if (empty($lead->practice_slug)) $lead->practice_slug = $this->uniqueSlug($lead->practice);
        if (empty($lead->home_path))     $lead->home_path     = '/practice/'.$lead->practice_slug;
        $lead->token = $lead->token ?: Str::random(40);
        $lead->save();

        try { Mail::to($lead->email)->send(new LeadConfirmMail($lead)); }
        catch (\Throwable $e) { Log::error('Lead confirm mail failed: '.$e->getMessage()); }

        return back()->with('ok','Confirmation email re-sent.');
    }
}
