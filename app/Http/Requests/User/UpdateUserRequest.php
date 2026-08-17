<?php

   namespace App\Http\Requests\User;

   use Illuminate\Contracts\Validation\Validator;
   use Illuminate\Foundation\Http\FormRequest;
   use Illuminate\Http\Exceptions\HttpResponseException;

   class UpdateUserRequest extends FormRequest
   {
      public function authorize(): bool {
         return $this->user()->hasPermissionTo('edit-users');
      }

      public function rules(): array {
         $userId = $this->route('user');

         return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'email'         => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password'      => ['sometimes', 'string', 'min:8'],
            'role'          => ['sometimes', 'string', 'exists:roles,name'],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
         ];
      }

      public function messages(): array {
         return [
            'email.unique'         => 'Email sudah terdaftar.',
            'password.min'         => 'Password minimal 8 karakter.',
            'role.exists'          => 'Role tidak ditemukan.',
            'permissions.*.exists' => 'Permission tidak ditemukan.',
         ];
      }

      protected function failedValidation(Validator $validator): void {
         throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors(),
         ], 422));
      }
   }