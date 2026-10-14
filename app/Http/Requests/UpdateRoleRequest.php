<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */


    public function rules()
    {
        return [
            'role_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'role_name')->ignore($this->route('role')->id),
            ],
            'role_details' => ['required', 'array', 'min:1'],
            'role_details.*.function_name' => [
                'required',
                'string',
                'exists:functions,function_name',
            ],

            'role_details.*.canView' => [
                'required',
                'boolean',
            ],

            'role_details.*.canCreate' => [
                'required',
                'boolean',
            ],

            'role_details.*.canUpdate' => [
                'required',
                'boolean',
            ],

            'role_details.*.canDelete' => [
                'required',
                'boolean',
            ],
        ];
    }
    
    public function messages()
    {
        return [
            'role_name.required' => ':attribute không được để trống',
            'role_name.string' => ':attribute phải là chuỗi ký tự',
            'role_name.max' => ':attribute không được vượt quá 255 ký tự',
            'role_name.unique' => ':attribute đã tồn tại',

            'role_details.required' => ':attribute không được để trống',
            'role_details.array' => ':attribute phải là mảng',
            'role_details.min' => ':attribute phải có ít nhất 1 phần tử',

            'role_details.*.function_name.required' => ':attribute không được để trống',
            'role_details.*.function_name.string' => ':attribute phải là chuỗi ký tự',
            'role_details.*.function_name.exists' => ':attribute không tồn tại trong hệ thống',

            'role_details.*.canView.required' => ':attribute không được để trống',
            'role_details.*.canView.boolean' => ':attribute phải là kiểu đúng/sai',
            'role_details.*.canCreate.required' => ':attribute không được để trống',
            'role_details.*.canCreate.boolean' => ':attribute phải là kiểu đúng/sai',
            'role_details.*.canUpdate.required' => ':attribute không được để trống',
            'role_details.*.canUpdate.boolean' => ':attribute phải là kiểu đúng/sai',
            'role_details.*.canDelete.required' => ':attribute không được để trống',
            'role_details.*.canDelete.boolean' => ':attribute phải là kiểu đúng/sai',
        ];
    }

    public function attributes()
    {
        return [
            'role_name' => 'Tên nhóm quyền',
            'role_details' => 'Danh sách chi tiết quyền',
            'role_details.*.function_name' => 'Tên chức năng',
            'role_details.*.canView' => 'Quyền xem',
            'role_details.*.canCreate' => 'Quyền tạo',
            'role_details.*.canUpdate' => 'Quyền cập nhật',
            'role_details.*.canDelete' => 'Quyền xóa',
        ];
    }
}
