<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $config = $this->config();
        $model = $this->route()?->parameter($this->route()?->parameterNames()[0] ?? '') ?? null;
        $id = is_object($model) ? $model->getKey() : $model;
        $rules = [];

        foreach (($config['rules'] ?? []) as $field => $fieldRules) {
            $rules[$field] = collect($fieldRules)
                ->map(function ($rule) use ($id) {
                    if ($rule === 'required_on_create') {
                        return $this->isMethod('post') ? 'required' : 'nullable';
                    }

                    if (is_string($rule) && str_starts_with($rule, 'unique:')) {
                        return str_replace('{id}', $id ? (string) $id : 'NULL', $rule);
                    }

                    return $rule;
                })
                ->all();
        }

        if (array_key_exists('is_active', $config['fields'] ?? [])) {
            $rules['is_active'][] = Rule::in([0, 1, '0', '1', true, false]);
        }

        if (($config['route'] ?? null) === 'master.role-permissions') {
            $rules['permission_id'][] = Rule::unique('sys_role_permissions', 'permission_id')
                ->where(fn ($query) => $query->where('role_id', $this->input('role_id')))
                ->ignore($id);
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $fields = $this->config()['fields'] ?? [];
        $merge = [];

        foreach ($fields as $name => $field) {
            if (($field['type'] ?? null) === 'boolean') {
                $merge[$name] = $this->boolean($name);
            }
        }

        if (($this->config()['route'] ?? null) === 'master.permissions' && $this->filled('name')) {
            $merge['code'] = str($this->input('name'))->lower()->replace(' ', '.')->replace('_', '-')->toString();
        }

        if (in_array(($this->config()['route'] ?? null), ['master.modules', 'master.actions'], true) && ! $this->filled('slug') && $this->filled('name')) {
            $merge['slug'] = str($this->input('name'))->slug()->toString();
        }

        $this->merge($merge);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (is_array($data) && array_key_exists('password', $data) && empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    private function config(): array
    {
        $routeName = $this->route()?->getName();

        foreach (config('master-data') as $config) {
            if ($routeName && str_starts_with($routeName, $config['route'].'.')) {
                return $config;
            }
        }

        return [];
    }
}
