<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(): View
    {
        $states = State::with('country')->get();
        $cities = City::query()->with(['state', 'country'])->paginate(10);
        return view('pages.cities.index', compact('cities', 'states'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {

            $state = State::query()->with('country')->findOrFail($request->get('state_id'));
            $data = $request->all();
            $data['country_id'] = $state->country_id;

            $city = City::query()->create($data);
            $city->refresh();

            return redirect()->route('cities.index')->with('success', 'City created successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function update(Request $request, $id): RedirectResponse
    {
        try {

            $city = City::query()->findOrFail($id);
            $state = State::query()->with('country')->findOrFail($request->get('state_id'));
            $data = $request->all();
            $data['country_id'] = $state->country_id;

            $city->update($data);
            $city->refresh();

            return redirect()->route('cities.index')->with('success', 'City updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function destroy($id): RedirectResponse
    {
        try {

            $city = City::query()->findOrFail($id);
            $city->delete();

            return redirect()->route('cities.index')->with('success', 'City deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cityChangeStatus($id): JsonResponse
    {
        $city = City::query()->findOrFail($id);
        if ($city->status == 1) {
            $city->status = 0;
            $city->save();
        } else {
            $city->status = 1;
            $city->save();
        }
        return response()->json(['status' => $city->status]);
    }
}
