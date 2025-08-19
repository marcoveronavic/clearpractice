<?php

namespace App\Http\Controllers;

use App\Mail\LeadConfirmMail;
use App\Models\Lead;
use App\Models\LeadUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function showStart()
    {
        return view('landing.get-started');
    }

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
        $lead->token = $lead->token ?: Str::random(40);
        $lead->save();

        Mail::to($lead->email)->send(new LeadConfirmMail($lead));

        return view('landing.thanks', ['lead' => $lead]);
    }

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
        $lead = Lead::where('token', $token)->firstOrFail();

        if (!$lead->confirmed_at) {
            return redirect()->route('lead.confirm', ['token' => $token]);
        }

        $users = $lead->users()->latest()->get();
        return view('landing.add-users', ['lead' => $lead, 'users' => $users]);
    }

    public function storeUsers(Request $request)
    {
        $token = $request->input('t');
        $lead = Lead::where('token', $token)->firstOrFail();

        $validated = $request->validate([
            'users' => ['required','array','min:1'],
            'users.*.first_name' => ['required','string','max:100'],
            'users.*.last_name'  => ['required','string','max:100'],
            'users.*.email'      => ['required','email','max:190'],
        ]);

        foreach ($validated['users'] as $u) {
            LeadUser::create([
                'lead_id'    => $lead->id,
                'first_name' => $u['first_name'],
                'last_name'  => $u['last_name'],
                'email'      => $u['email'],
            ]);
        }

        return redirect()->route('lead.users.show', ['t' => $lead->token])
            ->with('ok', 'Users added.');
    }
}
