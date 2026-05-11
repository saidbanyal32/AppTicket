<?php

namespace App\Http\Controllers\Master;

use App\Http\Requests\Master\MasterDataRequest;
use App\Models\Master\RefJabatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SysUserController extends BaseMasterController
{
    protected string $resourceKey = 'users';

    public function store(MasterDataRequest $request): RedirectResponse
    {
        $this->authorizeMasterAction('create');

        $data = $this->validatedData($request);
        $this->assertJabatanMatchesUnit($data);
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
        $this->assertJabatanMatchesUnit($data);
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

    protected function selectOptions(?Model $current = null): array
    {
        $options = parent::selectOptions($current);

        $options['jabatan'] = RefJabatan::query()
            ->orderBy('name')
            ->get(['id', 'name', 'unit_id'])
            ->map(fn (RefJabatan $jabatan) => [
                'id' => $jabatan->id,
                'label' => $jabatan->name,
                'attributes' => ['unit-id' => $jabatan->unit_id],
            ])
            ->all();

        return $options;
    }

    private function assertJabatanMatchesUnit(array $data): void
    {
        if (empty($data['unit_id']) || empty($data['jabatan_id'])) {
            return;
        }

        $matchesUnit = RefJabatan::query()
            ->whereKey($data['jabatan_id'])
            ->where('unit_id', $data['unit_id'])
            ->exists();

        if (! $matchesUnit) {
            throw ValidationException::withMessages([
                'jabatan_id' => 'Jabatan harus sesuai dengan unit yang dipilih.',
            ]);
        }
    }
}
