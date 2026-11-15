<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DoctorTalkRequest;
use App\Models\DoctorTalk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DoctorTalkController extends Controller
{
    public function index(): View
    {
        $talks = DoctorTalk::query()
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15);

        return view('admin.doctor-talks.index', compact('talks'));
    }

    public function create(): View
    {
        return view('admin.doctor-talks.create', [
            'talk' => new DoctorTalk([
                'is_active' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(DoctorTalkRequest $request): RedirectResponse
    {
        DoctorTalk::query()->create($this->talkPayload($request));

        return redirect()->route('admin.doctor-talks.index')->with('status', 'Doctor talk created.');
    }

    public function edit(DoctorTalk $doctorTalk): View
    {
        return view('admin.doctor-talks.edit', ['talk' => $doctorTalk]);
    }

    public function update(DoctorTalkRequest $request, DoctorTalk $doctorTalk): RedirectResponse
    {
        $doctorTalk->update($this->talkPayload($request, $doctorTalk->thumbnail));

        return redirect()->route('admin.doctor-talks.index')->with('status', 'Doctor talk updated.');
    }

    public function destroy(DoctorTalk $doctorTalk): RedirectResponse
    {
        $this->deleteStoredThumbnail($doctorTalk->thumbnail);
        $doctorTalk->delete();

        return redirect()->route('admin.doctor-talks.index')->with('status', 'Doctor talk deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function talkPayload(DoctorTalkRequest $request, ?string $currentThumbnail = null): array
    {
        $data = $request->safe()->except(['thumbnail_upload']);

        if ($request->hasFile('thumbnail_upload')) {
            $path = $request->file('thumbnail_upload')->store('doctor-talks', 'public');
            $data['thumbnail'] = '/storage/'.$path;
            $this->deleteStoredThumbnail($currentThumbnail);
        }

        return $data;
    }

    private function deleteStoredThumbnail(?string $thumbnail): void
    {
        if (! filled($thumbnail)) {
            return;
        }

        $path = parse_url($thumbnail, PHP_URL_PATH) ?: $thumbnail;

        if (! is_string($path) || ! str_contains($path, '/storage/doctor-talks/')) {
            return;
        }

        Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', $path), '/'));
    }
}
