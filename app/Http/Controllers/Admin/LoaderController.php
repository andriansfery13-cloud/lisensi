<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Services\LoaderGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class LoaderController extends Controller
{
    protected LoaderGeneratorService $loaderService;

    public function __construct(LoaderGeneratorService $loaderService)
    {
        $this->loaderService = $loaderService;
    }

    public function index()
    {
        $licenses = License::where('status', 'active')->get(['id', 'serial_number', 'product_name', 'customer_name']);
        return view('admin.loader.generate', compact('licenses'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|exists:licenses,serial_number',
            'api_url' => 'required|url',
            'obfuscated' => 'nullable|boolean',
            'cache_days' => 'nullable|integer|min:1|max:30',
            'heartbeat_hours' => 'nullable|integer|min:1|max:168',
        ]);

        $loaderCode = $this->loaderService->generate([
            'serial_number' => $validated['serial_number'],
            'api_url' => rtrim($validated['api_url'], '/'),
            'obfuscated' => $request->boolean('obfuscated'),
            'cache_days' => $validated['cache_days'] ?? 7,
            'heartbeat_hours' => $validated['heartbeat_hours'] ?? 24,
        ]);

        $cacheTemplate = $this->loaderService->generateCacheTemplate();
        $readme = $this->loaderService->generateReadme($validated['serial_number']);

        // Create ZIP
        $filename = 'license_loader_' . str_replace('-', '_', $validated['serial_number']) . '_' . date('Ymd_His') . '.zip';
        $zipPath = storage_path('app/generated/' . $filename);

        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFromString('license.php', $loaderCode);
            $zip->addFromString('license_cache.json', $cacheTemplate);
            $zip->addFromString('README.txt', $readme);
            $zip->close();
        }

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string',
            'api_url' => 'required|url',
            'obfuscated' => 'nullable|boolean',
        ]);

        $code = $this->loaderService->generate([
            'serial_number' => $validated['serial_number'],
            'api_url' => rtrim($validated['api_url'], '/'),
            'obfuscated' => $request->boolean('obfuscated'),
            'cache_days' => 7,
            'heartbeat_hours' => 24,
        ]);

        return response()->json(['code' => $code]);
    }
}
