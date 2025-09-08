<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Dcblogdev\MsGraph\Facades\MsGraph;
use App\Models\Practice;

class OneDriveController extends Controller
{
    /** Start Microsoft OAuth */
    public function connect()
    {
        return MsGraph::connect();
    }

    /** Landing: show status, root items, and bind OneDrive to a practice */
    public function landing(Request $request)
    {
        $drive = null; $driveId = null; $driveType = null; $children = [];

        try {
            $drive     = MsGraph::get('/me/drive'); // personal or business
            $driveId   = $drive['id'] ?? null;
            $driveType = $drive['driveType'] ?? 'unknown';
            $children  = MsGraph::get('/me/drive/root/children')['value'] ?? [];
        } catch (\Throwable $e) {
            // Not connected yet; show sign-in button.
        }

        // Resolve current practice reliably:
        // 1) if this page is opened inside /p/{practice}/..., the route contains it
        // 2) otherwise, pick the user's latest owned or member practice
        $practice = null;
        if ($request->route('practice') instanceof Practice) {
            $practice = $request->route('practice');
        } elseif ($request->user()) {
            $uid = $request->user()->id;
            $practice = Practice::where('owner_id', $uid)->latest('id')->first()
                ?: Practice::whereHas('members', fn($q) => $q->where('users.id', $uid))
                    ->latest('id')->first();
        }

        return view('integrations.onedrive.landing', compact('drive','driveId','driveType','children','practice'));
    }

    /** Create a folder in OneDrive root */
    public function createFolder(Request $request)
    {
        $request->validate([
            'folder_name' => 'required|string|max:200',
        ]);

        MsGraph::post('/me/drive/root/children', [
            'name'   => $request->folder_name,
            'folder' => (object)[],
            '@microsoft.graph.conflictBehavior' => 'rename',
        ]);

        return back()->with('status', 'Folder created.');
    }

    /** Save selected base folder to the practice */
    public function save(Request $request)
    {
        $request->validate([
            'practice_id' => 'required|exists:practices,id',
            'drive_id'    => 'required|string',
            'drive_type'  => 'required|string',
            'base_path'   => 'nullable|string',
        ]);

        $practice = Practice::findOrFail($request->practice_id);
        $practice->onedrive_drive_id   = $request->drive_id;
        $practice->onedrive_drive_type = $request->drive_type;
        $practice->onedrive_base_path  = trim($request->base_path ?? '', '/');
        $practice->save();

        return redirect()->route('onedrive.landing')->with('status', 'OneDrive connected to this practice.');
    }

    /** Smoke test upload into the saved base path */
    public function uploadTest(Request $request)
    {
        $request->validate([
            'practice_id' => 'required|exists:practices,id',
        ]);

        $practice = Practice::findOrFail($request->practice_id);

        if (!$practice->onedrive_drive_id) {
            return back()->withErrors('Connect OneDrive and choose a base folder first.');
        }

        $disk = Storage::build([
            'driver'  => 'msgraph',
            'driveId' => $practice->onedrive_drive_id,
        ]);

        $base = $practice->onedrive_base_path ? trim($practice->onedrive_base_path, '/').'/' : '';
        $disk->put($base.'clearpractice-hello.txt', "Hello from ClearPractice 👋");

        return back()->with('status', 'Uploaded test file to OneDrive.');
    }

    /* =========================
       In-app browser & openers
       ========================= */

    protected function encodePath(string $path): string
    {
        return rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
    }

    protected function decodePath(?string $encoded): string
    {
        if (!$encoded) return '';
        return base64_decode(strtr($encoded, '-_', '+/')) ?: '';
    }

    /** Browse inside a OneDrive path (relative to root) with breadcrumb */
    public function browse(?string $encoded = null)
    {
        try { MsGraph::get('/me/drive'); } catch (\Throwable $e) {
            return redirect()->route('onedrive.landing');
        }

        $pathRel = trim($this->decodePath($encoded), '/');

        $item = $pathRel === ''
            ? MsGraph::get('/me/drive/root')
            : MsGraph::get("/me/drive/root:/{$pathRel}");

        $webUrl   = $item['webUrl'] ?? null;
        $children = $pathRel === ''
            ? (MsGraph::get('/me/drive/root/children')['value'] ?? [])
            : (MsGraph::get("/me/drive/root:/{$pathRel}:/children")['value'] ?? []);

        $crumbs = [['name' => 'Root', 'encoded' => null]];
        if ($pathRel !== '') {
            $accum = '';
            foreach (explode('/', $pathRel) as $seg) {
                $accum = ltrim($accum.'/'.$seg, '/');
                $crumbs[] = ['name' => $seg, 'encoded' => $this->encodePath($accum)];
            }
        }

        return view('integrations.onedrive.browse', [
            'pathRel'  => $pathRel,
            'webUrl'   => $webUrl,
            'children' => $children,
            'crumbs'   => $crumbs,
            'encode'   => fn(string $p) => $this->encodePath($p),
        ]);
    }

    /** Open an item (folder/file) in OneDrive web */
    public function openInOneDrive(string $encoded)
    {
        $pathRel = trim($this->decodePath($encoded), '/');

        $item = $pathRel === ''
            ? MsGraph::get('/me/drive/root')
            : MsGraph::get("/me/drive/root:/{$pathRel}");

        $url = $item['webUrl'] ?? null;
        abort_unless($url, 404, 'Item not found');
        return redirect()->away($url);
    }
}
