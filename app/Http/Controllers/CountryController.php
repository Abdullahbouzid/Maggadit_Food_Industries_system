<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\City;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    // الصفحة الرئيسية (تعرض الخريطة وجدولين)
    public function index()
    {
        $countries = Country::with('cities')->get();
        $cities = City::with('country')->get();

        // تجهيز الماركرات للخريطة
        $markers = [];
        foreach ($countries as $country) {
            if ($country->latitude && $country->longitude) {
                $markers[] = [
                    'lat'  => (float)$country->latitude,
                    'lng'  => (float)$country->longitude,
                    'name' => $country->name,
                    'type' => 'Country',
                ];
            }
        }
        foreach ($cities as $city) {
            if ($city->latitude && $city->longitude) {
                $markers[] = [
                    'lat'  => (float)$city->latitude,
                    'lng'  => (float)$city->longitude,
                    'name' => $city->name . ' (' . ($city->country->name ?? 'N/A') . ')',
                    'type' => 'City',
                ];
            }
        }

        return view('dashboard.data_definition.master.General.GeographicalLocationHierarchy.index', compact('countries', 'cities', 'markers'));
    }

    public function create()
    {
        return view('dashboard.data_definition.master.General.GeographicalLocationHierarchy.countries.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:countries',
            'code'      => 'required|string|max:10|unique:countries',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        Country::create($validated);
        return redirect()->route('geographical.index')->with('success', 'Country created successfully.');
    }

    public function show(Country $country)
    {
        $country->load('cities');
        return view('dashboard.data_definition.master.General.GeographicalLocationHierarchy.countries.show', compact('country'));
    }

    public function edit(Country $country)
    {
        return view('dashboard.data_definition.master.General.GeographicalLocationHierarchy.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:countries,name,' . $country->id,
            'code'      => 'required|string|max:10|unique:countries,code,' . $country->id,
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $country->update($validated);
        return redirect()->route('geographical.index')->with('success', 'Country updated successfully.');
    }

    public function destroy(Country $country)
    {
        $country->delete();
        return redirect()->route('geographical.index')->with('success', 'Country deleted successfully.');
    }
}