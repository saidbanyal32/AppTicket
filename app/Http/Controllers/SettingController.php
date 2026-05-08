<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use App\Services\UiAuthorizationService;

class SettingController extends Controller
{
    public function __construct(private readonly UiAuthorizationService $access) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->access->authorizeResource('settings', 'view');

        return view('settings.index', [
            'title' => 'Settings',
            'subtitle' => 'Application and ticketing runtime configuration',
            'breadcrumbs' => [['label' => 'Desk', 'url' => route('home')], ['label' => 'System'], ['label' => 'Settings']],
            'settings' => Setting::orderBy('key')->paginate(25),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->access->authorizeResource('settings', 'create');

        return view('settings.form', $this->viewData(new Setting, 'create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SettingRequest $request)
    {
        $this->access->authorizeResource('settings', 'create');

        $setting = Setting::create($request->validated());

        return redirect()->route('settings.edit', $setting)->with('status', 'Setting berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        $this->access->authorizeResource('settings', 'view');

        return redirect()->route('settings.edit', $setting);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting)
    {
        $this->access->authorizeResource('settings', 'update');

        return view('settings.form', $this->viewData($setting, 'edit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SettingRequest $request, Setting $setting)
    {
        $this->access->authorizeResource('settings', 'update');

        $setting->update($request->validated());

        return redirect()->route('settings.edit', $setting)->with('status', 'Setting berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        $this->access->authorizeResource('settings', 'delete');

        $setting->delete();

        return redirect()->route('settings.index')->with('status', 'Setting berhasil dihapus.');
    }

    private function viewData(Setting $setting, string $mode): array
    {
        return [
            'title' => 'Settings',
            'subtitle' => 'Application and ticketing runtime configuration',
            'breadcrumbs' => [['label' => 'Desk', 'url' => route('home')], ['label' => 'System'], ['label' => 'Settings']],
            'setting' => $setting,
            'mode' => $mode,
        ];
    }
}
