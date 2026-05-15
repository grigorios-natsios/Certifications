<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateImageController extends Controller
{
    private const EXTS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
    private const MAX_KB = 5120; // 5MB

    public function index(): JsonResponse
    {
        $orgId = Auth::user()->organization_id;
        $dir   = $this->orgDir($orgId);

        $images = [];
        if (is_dir($dir)) {
            foreach (scandir($dir) ?: [] as $file) {
                if ($file === '.' || $file === '..') continue;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (! in_array($ext, self::EXTS, true)) continue;

                $abs = $dir.DIRECTORY_SEPARATOR.$file;
                $images[] = [
                    'name' => $file,
                    'url'  => $this->urlFor($orgId, $file),
                    'size' => is_file($abs) ? filesize($abs) : 0,
                    'mtime'=> is_file($abs) ? filemtime($abs) : 0,
                ];
            }
        }

        // Newest first
        usort($images, fn ($a, $b) => $b['mtime'] <=> $a['mtime']);

        return response()->json(['images' => $images]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:'.implode(',', self::EXTS).'|max:'.self::MAX_KB,
        ]);

        $orgId = Auth::user()->organization_id;
        $dir   = $this->orgDir($orgId);

        if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            return response()->json(['error' => 'Δεν δημιουργήθηκε ο φάκελος αποθήκευσης.'], 500);
        }

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());

        // Sanitize: keep only safe chars in the basename, append a short hash to
        // avoid collisions and to make filenames unguessable.
        $rawBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $base    = preg_replace('/[^A-Za-z0-9_-]+/u', '-', $rawBase);
        $base    = trim((string) $base, '-');
        if ($base === '') $base = 'image';
        $base    = mb_substr($base, 0, 60);

        $hash     = substr(bin2hex(random_bytes(4)), 0, 8);
        $filename = $base.'-'.$hash.'.'.$ext;

        $file->move($dir, $filename);

        return response()->json([
            'name' => $filename,
            'url'  => $this->urlFor($orgId, $filename),
        ]);
    }

    public function destroy(string $name): JsonResponse
    {
        $orgId = Auth::user()->organization_id;
        $safeName = basename($name); // strip any directory traversal
        $path = $this->orgDir($orgId).DIRECTORY_SEPARATOR.$safeName;

        if (is_file($path)) {
            @unlink($path);
        }

        return response()->json(['ok' => true]);
    }

    private function orgDir(int $orgId): string
    {
        return public_path('uploads'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$orgId);
    }

    private function urlFor(int $orgId, string $filename): string
    {
        return '/uploads/templates/'.$orgId.'/'.rawurlencode($filename);
    }
}
