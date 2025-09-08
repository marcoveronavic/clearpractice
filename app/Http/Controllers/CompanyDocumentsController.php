<?php

namespace App\Http\Controllers;

use App\Models\Practice;
use App\Models\Company; // adjust namespace if different
use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyDocumentsController extends Controller
{
    // Helper: practice's per-company folder (relative to drive root)
    protected function companyFolderRel(Practice $practice, Company $company): string
    {
        $base = $practice->oneDriveBase(); // '' or 'Base/'
        // Store under Base/companies/{company-slug}
        return $base . 'companies/' . $company->slug;
    }

    // GET – Documents tab (renders the panel)
    public function show(Practice $practice, Company $company)
    {
        $connected = $practice->hasOneDrive();

        $folderRel = $connected ? $this->companyFolderRel($practice, $company) : null;
        $items = [];
        $webUrl = null;

        if ($connected) {
            $driveId = $practice->onedrive_drive_id;

            // Try to fetch the folder info + children; if folder doesn't exist yet, children will be empty.
            try {
                $item = MsGraph::get("/drives/{$driveId}/root:/{$folderRel}");
                $webUrl = $item['webUrl'] ?? null;

                $resp = MsGraph::get("/drives/{$driveId}/root:/{$folderRel}:/children");
                $items = $resp['value'] ?? [];
            } catch (\Throwable $e) {
                // Folder not found yet; UI will offer to create it.
                $items = [];
            }
        }

        return view('companies.documents', [
            'practice'  => $practice,
            'company'   => $company,
            'connected' => $connected,
            'webUrl'    => $webUrl,
            'items'     => $items,     // Graph items (name,size,lastModifiedDateTime,folder/file, id)
            'folderRel' => $folderRel, // e.g. "Base/companies/acme-ltd"
        ]);
    }

    // POST – Create the per-company folder
    public function createFolder(Request $request, Practice $practice, Company $company)
    {
        if (! $practice->hasOneDrive()) {
            return back()->withErrors('Connect OneDrive first (Settings → OneDrive).');
        }

        $disk = Storage::build([
            'driver'  => 'msgraph',
            'driveId' => $practice->onedrive_drive_id,
        ]);

        $disk->makeDirectory($this->companyFolderRel($practice, $company));

        return back()->with('status', 'Company folder created on OneDrive.');
    }

    // POST – Upload a file to the per-company folder
    public function upload(Request $request, Practice $practice, Company $company)
    {
        $request->validate(['file' => 'required|file|max:51200']); // 50 MB demo limit
        if (! $practice->hasOneDrive()) {
            return back()->withErrors('Connect OneDrive first (Settings → OneDrive).');
        }

        $disk = Storage::build([
            'driver'  => 'msgraph',
            'driveId' => $practice->onedrive_drive_id,
        ]);

        $folder = $this->companyFolderRel($practice, $company);
        $name   = $request->file('file')->getClientOriginalName();

        $disk->putFileAs($folder, $request->file('file'), $name);

        return back()->with('status', "Uploaded {$name}.");
    }

    // GET – Download a file (path is base64url-encoded)
    public function download(Practice $practice, Company $company, string $encoded)
    {
        if (! $practice->hasOneDrive()) {
            abort(404);
        }

        $pathRel = $this->decodePath($encoded);
        $disk = Storage::build([
            'driver'  => 'msgraph',
            'driveId' => $practice->onedrive_drive_id,
        ]);

        return $disk->download($pathRel);
    }

    // DELETE – Delete a file (path is base64url-encoded)
    public function delete(Request $request, Practice $practice, Company $company, string $encoded)
    {
        if (! $practice->hasOneDrive()) {
            return back()->withErrors('OneDrive not connected.');
        }

        $pathRel = $this->decodePath($encoded);
        $disk = Storage::build([
            'driver'  => 'msgraph',
            'driveId' => $practice->onedrive_drive_id,
        ]);

        $disk->delete($pathRel);

        return back()->with('status', 'File deleted.');
    }

    // --- base64url helpers (so we can pass paths safely in URLs) ---
    protected function encodePath(string $path): string
    {
        return rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
    }

    protected function decodePath(string $encoded): string
    {
        return base64_decode(strtr($encoded, '-_', '+/'));
    }
}
