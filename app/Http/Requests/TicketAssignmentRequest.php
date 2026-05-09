<?php

namespace App\Http\Requests;

use App\Models\Master\SysUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TicketAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assigned_to' => [
                'required',
                Rule::exists('sys_users', 'id')->where(fn ($query) => $query->whereIn(
                    'id',
                    DB::table('sys_user_roles')
                        ->join('sys_roles', 'sys_roles.id', '=', 'sys_user_roles.role_id')
                        ->where('sys_roles.code', 'PICTICKET')
                        ->where('sys_user_roles.model_type', SysUser::class)
                        ->select('sys_user_roles.model_id')
                )),
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
