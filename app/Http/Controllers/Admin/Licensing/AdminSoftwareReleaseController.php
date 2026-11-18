<?php

namespace App\Http\Controllers\Admin\Licensing;

use App\Http\Controllers\Controller;
use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use App\Services\Licensing\ReleaseManagerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminSoftwareReleaseController extends Controller
{
    public function __construct(private readonly ReleaseManagerService $releaseManager) {}

    /**
     * Display a listing of software releases.
     */
    public function index(Request $request): View
    {
        $productId = (string) $request->query('product_id', '');

        $releases = SoftwareRelease::query()
            ->with('product')
            ->when($productId !== '', fn ($q) => $q->where('product_id', $productId))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $products = SoftwareProduct::query()->orderBy('name')->get();

        return view('admin.licensing.releases.index', [
            'releases' => $releases,
            'products' => $products,
            'productId' => $productId,
        ]);
    }

    /**
     * Show the form for creating a new software release.
     */
    public function create(): View
    {
        $products = SoftwareProduct::where('is_active', true)->orderBy('name')->get();

        return view('admin.licensing.releases.create', [
            'products' => $products,
            'release' => new SoftwareRelease([
                'min_php_version' => '8.2.0',
                'is_active' => true,
                'is_prerelease' => false,
            ]),
        ]);
    }

    /**
     * Store a newly created release package.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:software_products,id'],
            'version' => [
                'required',
                'string',
                'max:30',
                Rule::unique('software_releases')->where('product_id', $request->input('product_id')),
            ],
            'min_php_version' => ['required', 'string', 'max:10'],
            'changelog' => ['nullable', 'string'],
            'release_file' => ['nullable', 'file', 'mimes:zip', 'max:102400'], // max 100MB
            'file_path' => ['nullable', 'string', 'max:255'],
            'sha256_checksum' => ['nullable', 'string', 'size:64'],
            'is_prerelease' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $product = SoftwareProduct::findOrFail($validated['product_id']);

        if ($request->hasFile('release_file')) {
            $archiveMeta = $this->releaseManager->storeReleaseArchive(
                $request->file('release_file'),
                $product,
                $validated['version']
            );

            $filePath = $archiveMeta['file_path'];
            $fileSize = $archiveMeta['file_size_bytes'];
            $checksum = $archiveMeta['sha256_checksum'];
        } else {
            $filePath = $validated['file_path'] ?? 'releases/manual-placeholder-'.$validated['version'].'.zip';
            $fileSize = 0;
            $checksum = $validated['sha256_checksum'] ?? hash('sha256', $validated['version']);
        }

        SoftwareRelease::create([
            'product_id' => $product->id,
            'version' => $validated['version'],
            'min_php_version' => $validated['min_php_version'],
            'changelog' => $validated['changelog'] ?? '',
            'file_path' => $filePath,
            'file_size_bytes' => $fileSize,
            'sha256_checksum' => $checksum,
            'is_prerelease' => $request->boolean('is_prerelease'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.licensing.releases.index')
            ->with('status', "Release [v{$validated['version']}] published successfully.");
    }

    /**
     * Show form for editing an existing release.
     */
    public function edit(SoftwareRelease $release): View
    {
        $products = SoftwareProduct::query()->orderBy('name')->get();

        return view('admin.licensing.releases.edit', [
            'release' => $release,
            'products' => $products,
        ]);
    }

    /**
     * Update release record.
     */
    public function update(Request $request, SoftwareRelease $release): RedirectResponse
    {
        $validated = $request->validate([
            'min_php_version' => ['required', 'string', 'max:10'],
            'changelog' => ['nullable', 'string'],
            'is_prerelease' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $release->update([
            'min_php_version' => $validated['min_php_version'],
            'changelog' => $validated['changelog'] ?? $release->changelog,
            'is_prerelease' => $request->boolean('is_prerelease'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.licensing.releases.index')
            ->with('status', "Release [v{$release->version}] updated.");
    }

    /**
     * Admin direct download of the release package for inspection.
     */
    public function download(SoftwareRelease $release): BinaryFileResponse|RedirectResponse
    {
        $filename = "{$release->product?->slug}-v{$release->version}.zip";

        if (Storage::disk('local')->exists($release->file_path)) {
            return response()->download(
                Storage::disk('local')->path($release->file_path),
                $filename,
                ['Content-Type' => 'application/zip']
            );
        }

        $fallbackPath = storage_path('app/'.$release->file_path);
        if (file_exists($fallbackPath)) {
            return response()->download($fallbackPath, $filename, ['Content-Type' => 'application/zip']);
        }

        return back()->withErrors(['error' => 'Release archive file was not found on server storage.']);
    }

    /**
     * Verify physical checksum on disk against recorded SHA-256 hash.
     */
    public function verifyIntegrity(SoftwareRelease $release): RedirectResponse
    {
        $isValid = $this->releaseManager->verifyReleaseIntegrity($release);

        if ($isValid) {
            return back()->with('status', "Integrity Verified: Release [v{$release->version}] SHA-256 matches package on disk.");
        }

        return back()->withErrors(['error' => "Integrity Warning: File for release [v{$release->version}] is missing or checksum does not match."]);
    }

    /**
     * Delete a release record.
     */
    public function destroy(SoftwareRelease $release): RedirectResponse
    {
        if (Storage::disk('local')->exists($release->file_path)) {
            Storage::disk('local')->delete($release->file_path);
        }

        $release->delete();

        return redirect()
            ->route('admin.licensing.releases.index')
            ->with('status', 'Release removed.');
    }
}
