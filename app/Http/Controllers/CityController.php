<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::with('country', 'state')->get();

        return view('dashboard.data_definition.master.General.GeographicalLocationHierarchy.cities.index', compact('cities'));
    }

    public function create()
    {
        $countries = Country::orderBy('name')->get();
        // ✅ إضافة قائمة الولايات مرتبة حسب الاسم الإنجليزي
        $states = State::orderBy('name_en')->get();

        return view('dashboard.data_definition.master.General.GeographicalLocationHierarchy.cities.create', compact('countries', 'states'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',  // ✅ إضافة التحقق على state_id
            'name' => 'required|string|max:255|unique:cities,name,NULL,id,country_id,'.$request->country_id,
            'code' => 'nullable|string|max:20|unique:cities,code,NULL,id,country_id,'.$request->country_id,
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        City::create($validated);

        return redirect()->route('cities.index')->with('success', 'City created successfully.');
    }

    public function show(City $city)
    {
        $city->load('country', 'state');

        return view('dashboard.data_definition.master.General.GeographicalLocationHierarchy.cities.show', compact('city'));
    }

    public function edit(City $city)
    {
        $countries = Country::orderBy('name')->get();
        $states = State::orderBy('name_en')->get();

        return view('dashboard.data_definition.master.General.GeographicalLocationHierarchy.cities.edit', compact('city', 'countries', 'states'));
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',  // ✅ إضافة التحقق على state_id
            'name' => 'required|string|max:255|unique:cities,name,'.$city->id.',id,country_id,'.$request->country_id,
            'code' => 'nullable|string|max:20|unique:cities,code,'.$city->id.',id,country_id,'.$request->country_id,
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $city->update($validated);

        return redirect()->route('cities.index')->with('success', 'City updated successfully.');
    }

    public function destroy(City $city)
    {
        $city->delete();

        return redirect()->route('cities.index')->with('success', 'City deleted successfully.');
    }
}
