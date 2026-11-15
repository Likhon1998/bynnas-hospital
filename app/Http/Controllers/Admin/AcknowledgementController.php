<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AcknowledgementRequest;
use App\Models\Acknowledgement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcknowledgementController extends Controller
{
    public function index(): View
    {
        $acknowledgements = Acknowledgement::query()
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15);

        return view('admin.acknowledgements.index', compact('acknowledgements'));
    }

    public function create(): View
    {
        return view('admin.acknowledgements.create', [
            'acknowledgement' => new Acknowledgement([
                'is_active' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(AcknowledgementRequest $request): RedirectResponse
    {
        Acknowledgement::query()->create($request->safe()->except(['_token']));

        return redirect()->route('admin.acknowledgements.index')->with('status', 'Acknowledgement created.');
    }

    public function edit(Acknowledgement $acknowledgement): View
    {
        return view('admin.acknowledgements.edit', compact('acknowledgement'));
    }

    public function update(AcknowledgementRequest $request, Acknowledgement $acknowledgement): RedirectResponse
    {
        $acknowledgement->update($request->safe()->except(['_token']));

        return redirect()->route('admin.acknowledgements.index')->with('status', 'Acknowledgement updated.');
    }

    public function destroy(Acknowledgement $acknowledgement): RedirectResponse
    {
        $acknowledgement->delete();

        return redirect()->route('admin.acknowledgements.index')->with('status', 'Acknowledgement deleted.');
    }
}
