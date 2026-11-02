<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __invoke(): View
    {
        $departments = Department::query()
            ->active()
            ->withCount([
                'doctors as consultants_count' => fn (Builder $query) => $query->active(),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('web.departments', [
            'departments' => $departments,
            'consultantTotal' => Doctor::query()->active()->count(),
        ]);
    }
}
