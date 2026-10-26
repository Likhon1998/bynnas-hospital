<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bed;
use Illuminate\View\View;

class CapacityForecastController extends Controller
{
    public function __invoke(): View
    {
        $totalBeds = Bed::query()->count();
        $occupiedNow = Bed::query()->where('status', 'occupied')->count();
        $availableNow = Bed::query()->where('status', 'available')->count();

        $forecast = collect(range(0, 6))->map(function (int $offset) use ($totalBeds, $occupiedNow): array {
            $day = now()->startOfDay()->addDays($offset);
            $booked = Appointment::query()
                ->whereDate('appointment_date', $day->toDateString())
                ->whereNotIn('status', ['cancelled'])
                ->count();

            $projected = min($totalBeds, $occupiedNow + (int) round($booked * 0.35) - (int) round($offset * 0.8));

            return [
                'day' => $day->format('D d M'),
                'booked' => $booked,
                'projected_occupied' => max(0, $projected),
                'projected_available' => max(0, $totalBeds - max(0, $projected)),
                'load' => $totalBeds > 0 ? (int) round((max(0, $projected) / $totalBeds) * 100) : 0,
            ];
        })->all();

        return view('admin.operations.forecasts', [
            'totalBeds' => $totalBeds,
            'occupiedNow' => $occupiedNow,
            'availableNow' => $availableNow,
            'forecast' => $forecast,
            'peakDay' => collect($forecast)->sortByDesc('load')->first(),
        ]);
    }
}
