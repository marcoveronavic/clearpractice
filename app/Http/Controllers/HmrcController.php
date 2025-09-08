<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Practice;
use App\Models\HmrcToken;
use App\Services\HmrcClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HmrcController extends Controller
{
    // GET /p/{practice:slug}/companies/{company}/hmrc/connect
    public function connectForCompany(Request $request, Practice $practice, Company $company)
    {
        if (!$company->vat_number) {
            return back()->withErrors(['vrn' => 'Add a VAT Registration Number on the company first.']);
        }

        $state = base64_encode(json_encode([
            'uid' => $request->user()->id,
            'p'   => $practice->slug,
            'c'   => $company->id,
            'vrn' => $company->vat_number,
            'n'   => (string) Str::uuid(),
        ]));

        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => config('hmrc.client_id'),
            'scope'         => config('hmrc.scope', 'read:vat'),
            'redirect_uri'  => config('hmrc.redirect_uri'),
            'state'         => $state,
        ]);

        return redirect(config('hmrc.auth_url').'?'.$query);
    }

    // GET /hmrc/callback  (redirected here by HMRC)
    public function callback(Request $request, HmrcClient $hmrc)
    {
        $code  = (string) $request->query('code');
        $state = json_decode(base64_decode((string) $request->query('state')), true) ?: [];

        if (!$code || empty($state['c']) || empty($state['p'])) {
            abort(400, 'Missing code/state');
        }

        $tokens = $hmrc->exchangeCode($code);

        HmrcToken::updateOrCreate(
            [
                'user_id'    => $request->user()->id,
                'company_id' => $state['c'],
            ],
            [
                'vrn'           => $state['vrn'] ?? null,
                'access_token'  => $tokens['access_token'] ?? null,
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'token_type'    => $tokens['token_type'] ?? 'Bearer',
                'scope'         => $tokens['scope'] ?? config('hmrc.scope', 'read:vat'),
                'expires_at'    => now()->addSeconds((int)($tokens['expires_in'] ?? 3600)),
            ]
        );

        return redirect()
            ->route('practice.companies.show', [$state['p'], $state['c']])
            ->with('status', 'HMRC VAT (MTD) connected for VRN '.$state['vrn']);
    }

    // GET /p/{practice:slug}/companies/{company}/hmrc/obligations
    public function obligationsForCompany(Request $request, Practice $practice, Company $company, HmrcClient $hmrc)
    {
        $token = HmrcToken::where('user_id', $request->user()->id)
            ->where('company_id', $company->id)->first();

        if (!$token) {
            return redirect()->route('practice.hmrc.connect', [$practice->slug, $company->id])
                ->withErrors(['hmrc' => 'Connect HMRC VAT first.']);
        }

        if (optional($token->expires_at)->isPast() && $token->refresh_token) {
            $new = $hmrc->refresh($token->refresh_token);
            $token->access_token  = $new['access_token'] ?? $token->access_token;
            $token->refresh_token = $new['refresh_token'] ?? $token->refresh_token;
            $token->expires_at    = now()->addSeconds((int)($new['expires_in'] ?? 3600));
            $token->save();
        }

        $params = array_filter([
            'status' => $request->query('status'),    // e.g. 'O' for open
            'from'   => $request->query('from'),      // yyyy-mm-dd
            'to'     => $request->query('to'),        // yyyy-mm-dd
        ]);

        $data = $hmrc->getObligations($token->access_token, $token->vrn ?? $company->vat_number, $params);

        return response()->json($data);
    }
}
