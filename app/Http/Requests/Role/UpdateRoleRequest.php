<?php

   namespace App\Http\Requests\Role;

   use Illuminate\Foundation\Http\FormRequest;
   use Illuminate\Contracts\Validation\Validator;
   use Illuminate\Http\Exceptions\HttpResponseException;

   class UpdateRoleRequest extends FormRequest
   {
      public function authorize(): bool {
         return $this->user()->hasPermissionTo('edit-roles');
      }

      public function rules(): array {
         return [
            'name'          => ['sometimes', 'string', 'max:255', 'unique:roles,name,' . $this->route('role')],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
         ];
      }

      protected function failedValidation(Validator $validator): void {
         throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors(),
         ], 422));
      }
   }