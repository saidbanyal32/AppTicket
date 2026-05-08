<?php

namespace App\Http\Controllers\Master;

use App\Http\Requests\Master\MasterDataRequest;
use Illuminate\Http\RedirectResponse;

class RoleController extends BaseMasterController
{
    protected string $resourceKey = 'roles';

    public function store(MasterDataRequest $request): RedirectResponse
    {
        $this->authorizeMasterAction('create');

        $data = $this->validatedData($request);
        $permissionIds = $data['permission_ids'] ?? [];
        unset($data['permission_ids']);
        $data['guard_name'] = 'web';

        $record = $this->newModel();
        $record->fill($data);
        $record->save();

        if (! empty($permissionIds)) {
            $record->permissions()->sync($permissionIds);
        }

        return redirect()->route($this->config('route').'.show', $record)
            ->with('status', $this->config('title').' berhasil dibuat.');
    }

    public function update(MasterDataRequest $request, mixed $record): RedirectResponse
    {
        $this->authorizeMasterAction('update');

        $record = $this->findRecord($record);
        $data = $this->validatedData($request, $record);
        $permissionIds = $data['permission_ids'] ?? [];
        unset($data['permission_ids']);
        $data['guard_name'] = 'web';

        $record->fill($data);
        $record->save();

        if (array_key_exists('permission_ids', $request->validated())) {
            $record->permissions()->sync($permissionIds);
        }

        return redirect()->route($this->config('route').'.show', $record)
            ->with('status', $this->config('title').' berhasil diperbarui.');
    }
}
