<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AirlineController extends Controller
{
    public function index(Request $request): View
    {
        return $this->view($request);
    }

    public function store(Request $request): RedirectResponse
    {
        $airline = Airline::create($this->validateAirline($request));

        return to_route('airlines.index', ['airline_id' => $airline->id])
            ->with('success', 'Авиакомпания добавлена.');
    }

    public function edit(Request $request, Airline $airline): View
    {
        return $this->view($request, $airline);
    }

    public function update(Request $request, Airline $airline): RedirectResponse
    {
        $airline->update($this->validateAirline($request, $airline));

        return to_route('airlines.index', ['airline_id' => $airline->id])
            ->with('success', 'Авиакомпания обновлена.');
    }

    public function destroy(Airline $airline): RedirectResponse
    {
        $airline->delete();

        return to_route('airlines.index')->with('success', 'Авиакомпания удалена.');
    }

    private function view(Request $request, ?Airline $editingAirline = null): View
    {
        $search = trim((string) $request->query('search', ''));
        $selectedAirlineId = $request->integer('airline_id') ?: null;

        $airlines = Airline::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            })
            ->withCount('flights')
            ->orderBy('name')
            ->get();

        $selectedAirline = $selectedAirlineId ? Airline::find($selectedAirlineId) : null;

        $linkedFlights = Flight::query()
            ->with('airline')
            ->when($selectedAirline, fn ($query) => $query->where('airline_id', $selectedAirline->id))
            ->orderBy('departure_at')
            ->get();

        return view('airlines.index', [
            'airlines' => $airlines,
            'editingAirline' => $editingAirline,
            'linkedFlights' => $linkedFlights,
            'search' => $search,
            'selectedAirline' => $selectedAirline,
        ]);
    }

    private function validateAirline(Request $request, ?Airline $airline = null): array
    {
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'code' => str($request->input('code', ''))->trim()->upper()->toString(),
            'country' => trim((string) $request->input('country', '')),
            'phone' => $request->filled('phone') ? trim((string) $request->input('phone')) : null,
        ]);

        return $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'code' => [
                    'required',
                    'string',
                    'max:8',
                    'regex:/^[A-Z0-9-]+$/',
                    Rule::unique('airlines', 'code')->ignore($airline),
                ],
                'country' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:32'],
            ],
            [],
            [
                'name' => 'название',
                'code' => 'код',
                'country' => 'страна',
                'phone' => 'телефон',
            ],
        );
    }
}
