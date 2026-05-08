<?php

namespace App\Http\Controllers\Master;

use App\Http\Requests\Master\MasterDataRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SysUserController extends BaseMasterController
{
    protected string $resourceKey = 'users';

    public function store(MasterDataRequest $request): RedirectResponse
    {
        $this->authorizeMasterAction('create');

        $data = $this->validatedData($request);
        $roleIds = $data['role_ids'] ?? [];
        unset($data['role_ids']);

        $record = $this->newModel();
        $record->fill($data);
        $record->save();
        $record->roles()->sync($roleIds);

        return redirect()->route($this->config('route').'.show', $record)
            ->with('status', $this->config('title').' berhasil dibuat.');
    }

    public function update(MasterDataRequest $request, mixed $record): RedirectResponse
    {
        $this->authorizeMasterAction('update');

        $record = $this->findRecord($record);
        $data = $this->validatedData($request, $record);
        $roleIds = $data['role_ids'] ?? [];
        unset($data['role_ids']);

        $record->fill($data);
        $record->save();
        $record->roles()->sync($roleIds);

        return redirect()->route($this->config('route').'.show', $record)
            ->with('status', $this->config('title').' berhasil diperbarui.');
    }

    public function resetPassword(mixed $user): RedirectResponse
    {
        $this->authorizeMasterAction('update');

        $record = $this->findRecord($user);
        $temporaryPassword = Str::password(12);

        $record->forceFill(['password' => Hash::make($temporaryPassword)])->save();

        return back()->with('status', 'Password sementara: '.$temporaryPassword);
    }
}
