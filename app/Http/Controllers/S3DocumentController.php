<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Models\Practice;
use App\Models\Company;

class S3DocumentController extends Controller
{
    /* ===== Utilities ===== */

    protected function resolveCompany(Practice $practice, string $companyParam): Company
    {
        $q = Company::query();
        $slugGuess = Str::slug($companyParam);

        $q->when(is_numeric($companyParam), fn($qq) => $qq->orWhere('id', $companyParam))
            ->orWhere('company_number', $companyParam)
            ->orWhereRaw('LOWER(name) = ?', [strtolower(str_replace('-', ' ', $companyParam))]);

        if (Schema::hasColumn('companies','slug')) {
            $q->orWhere('slug', $slugGuess);
        }

        $company = $q->firstOrFail();

        // Ensure the authed user can access this company (expects user->companies())
        if (!auth()->user()->companies()->where('companies.id', $company->id)->exists()) {
            abort(403, 'You do not have access to this company.');
        }

        return $company;
    }

    protected function companyPrefix(Practice $practice, Company $company): string
    {
        $base = $practice->s3_prefix ? trim($practice->s3_prefix, '/').'/' : '';
        $slug = $company->slug ?: Str::slug($company->name) ?: ('company-'.$company->id);
        return $base.'companies/'.$slug.'/';
    }

    protected function enc(string $path): string
    {
        return rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
    }

    protected function dec(string $encoded): string
    {
        return base64_decode(strtr($encoded, '-_', '+/')) ?: '';
    }

    /* ===== Practice-scoped S3 settings ===== */

    public function settings(Practice $practice)
    {
        return view('integrations.s3.landing', [
            'practice' => $practice,
            'bucket'   => env('AWS_BUCKET'),
            'base'     => $practice->s3_prefix ? trim($practice->s3_prefix, '/').'/' : '',
            'dirs'     => [],
            'files'    => [],
        ]);
    }

    public function saveSettings(Request $request, Practice $practice)
    {
        $data = $request->validate([
            's3_bucket' => ['required','string'],
            's3_prefix' => ['nullable','string'],
        ]);

        $practice->s3_bucket = $data['s3_bucket'];
        $practice->s3_prefix = trim($data['s3_prefix'] ?? '', '/');
        $practice->save();

        return back()->with('status','S3 settings saved.');
    }

    /* ===== Company documents (S3) ===== */

    public function showCompany(Practice $practice, string $companyParam)
    {
        abort_unless(!empty($practice->s3_bucket ?? env('AWS_BUCKET')), 403, 'S3 not configured for this practice.');

        $company = $this->resolveCompany($practice, $companyParam);

        $disk  = Storage::disk('s3');
        $pref  = $this->companyPrefix($practice, $company);

        $dirs = $files = [];
        try {
            $dirs  = $disk->directories($pref);
            $files = $disk->files($pref);
        } catch (\Throwable $e) {
            // first visit: empty ok
        }

        $relDirs  = array_map(fn($d)=> trim(Str::after($d,$pref), '/'), $dirs);
        $relFiles = array_map(fn($f)=> trim(Str::after($f,$pref), '/'), $files);

        return view('companies.s3', [
            'practice' => $practice,
            'company'  => $company,
            'prefix'   => $pref,
            'dirs'     => $relDirs,
            'files'    => $relFiles,
            'enc'      => fn($p) => $this->enc($p),
        ]);
    }

    public function createFolder(Request $request, Practice $practice, string $companyParam)
    {
        $company = $this->resolveCompany($practice, $companyParam);
        $data = $request->validate(['folder_name'=>'required|string|max:200']);

        $disk = Storage::disk('s3');
        $path = $this->companyPrefix($practice,$company);
        $name = str_replace('\\','/', trim($data['folder_name']));
        abort_if(str_contains($name, '..'), 422, 'Invalid folder name');

        $disk->makeDirectory($path.trim($name,'/').'/');

        return back()->with('status', 'Folder created.');
    }

    public function upload(Request $request, Practice $practice, string $companyParam)
    {
        $company = $this->resolveCompany($practice, $companyParam);
        $data = $request->validate(['file'=>'required|file|max:51200']); // 50MB demo

        $disk = Storage::disk('s3');
        $path = $this->companyPrefix($practice,$company);
        $name = $request->file('file')->getClientOriginalName();

        $disk->putFileAs($path, $request->file('file'), $name);

        return back()->with('status',"Uploaded {$name}.");
    }

    public function preview(Practice $practice, string $companyParam, string $encoded)
    {
        $company = $this->resolveCompany($practice, $companyParam);
        $rel  = $this->dec($encoded);
        $path = $this->companyPrefix($practice,$company).ltrim($rel,'/');

        $disk = Storage::disk('s3');

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf' => 'application/pdf',
            'jpg','jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream'
        };

        try {
            $stream = $disk->readStream($path);
            abort_unless($stream, 404, 'File not found');
            return response()->stream(function() use ($stream){ fpassthru($stream); }, 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.basename($path).'"',
                'Cache-Control' => 'private, max-age=0, no-cache',
            ]);
        } catch (\Throwable $e) {
            // fallback to presigned inline
            $url = $disk->temporaryUrl($path, now()->addMinutes(5), [
                'ResponseContentDisposition' => 'inline; filename="'.basename($path).'"',
                'ResponseContentType' => $mime,
            ]);
            return redirect()->away($url);
        }
    }

    public function open(Practice $practice, string $companyParam, string $encoded)
    {
        $company = $this->resolveCompany($practice, $companyParam);
        $rel  = $this->dec($encoded);
        $path = $this->companyPrefix($practice,$company).ltrim($rel,'/');

        $disk = Storage::disk('s3');

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf' => 'application/pdf',
            'jpg','jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream'
        };

        $url = $disk->temporaryUrl($path, now()->addMinutes(5), [
            'ResponseContentDisposition' => 'inline; filename="'.basename($path).'"',
            'ResponseContentType' => $mime,
        ]);
        return redirect()->away($url);
    }

    public function download(Practice $practice, string $companyParam, string $encoded)
    {
        $company = $this->resolveCompany($practice, $companyParam);
        $rel  = $this->dec($encoded);
        $path = $this->companyPrefix($practice,$company).ltrim($rel,'/');

        return Storage::disk('s3')->download($path);
    }

    public function share(Practice $practice, string $companyParam, string $encoded)
    {
        $company = $this->resolveCompany($practice, $companyParam);
        $rel  = $this->dec($encoded);
        $path = $this->companyPrefix($practice,$company).ltrim($rel,'/');

        $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(30));
        return back()->with('status','Share link created')->with('share_url',$url);
    }

    public function delete(Practice $practice, string $companyParam, string $encoded)
    {
        $company = $this->resolveCompany($practice, $companyParam);
        $rel  = $this->dec($encoded);
        $path = $this->companyPrefix($practice,$company).ltrim($rel,'/');

        Storage::disk('s3')->delete($path);

        return back()->with('status','Deleted.');
    }
}

