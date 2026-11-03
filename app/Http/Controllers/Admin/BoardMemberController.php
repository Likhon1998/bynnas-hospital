<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BoardMemberRequest;
use App\Models\BoardMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BoardMemberController extends Controller
{
    public function index(): View
    {
        $boardMembers = BoardMember::query()
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(20);

        return view('admin.board-members.index', compact('boardMembers'));
    }

    public function create(): View
    {
        return view('admin.board-members.create', [
            'boardMember' => new BoardMember(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(BoardMemberRequest $request): RedirectResponse
    {
        BoardMember::query()->create($this->memberPayload($request));

        return redirect()->route('admin.board-members.index')->with('status', 'Board member added. Active members appear on the public About page.');
    }

    public function edit(BoardMember $boardMember): View
    {
        return view('admin.board-members.edit', compact('boardMember'));
    }

    public function update(BoardMemberRequest $request, BoardMember $boardMember): RedirectResponse
    {
        $boardMember->update($this->memberPayload($request, $boardMember->photo));

        return redirect()->route('admin.board-members.index')->with('status', 'Board member updated.');
    }

    public function destroy(BoardMember $boardMember): RedirectResponse
    {
        $this->deleteStoredPhoto($boardMember->photo);
        $boardMember->delete();

        return redirect()->route('admin.board-members.index')->with('status', 'Board member removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function memberPayload(BoardMemberRequest $request, ?string $currentPhoto = null): array
    {
        $data = $request->safe()->except(['photo_upload']);

        if ($request->hasFile('photo_upload')) {
            $path = $request->file('photo_upload')->store('board', 'public');
            $data['photo'] = '/storage/'.$path;
            $this->deleteStoredPhoto($currentPhoto);
        }

        return $data;
    }

    private function deleteStoredPhoto(?string $photo): void
    {
        if (! filled($photo)) {
            return;
        }

        $path = parse_url($photo, PHP_URL_PATH) ?: $photo;

        if (! is_string($path) || ! str_contains($path, '/storage/board/')) {
            return;
        }

        Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', $path), '/'));
    }
}
