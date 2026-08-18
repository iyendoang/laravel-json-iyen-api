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
            'avatar'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'remove_avatar' => ['sometimes', 'boolean'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'bio'           => ['nullable', 'string', 'max:500'],
            'address'       => ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:100'],
            'country'       => ['nullable', 'string', 'max:100'],
            'postal_code'   => ['nullable', 'string', 'max:10'],
            'role'          => ['sometimes', 'string', 'exists:roles,name'],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
         ];
      }

      public function messages(): array {
         return [
            'email.unique'         => 'Email sudah terdaftar.',
            'password.min'         => 'Password minimal 8 karakter.',
            'avatar.image'         => 'File harus berupa gambar.',
            'avatar.mimes'         => 'Format gambar: jpeg, png, jpg, gif, webp.',
            'avatar.max'           => 'Ukuran gambar maksimal 2MB.',
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