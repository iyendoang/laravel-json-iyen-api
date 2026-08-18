<?php

   namespace App\Http\Requests\User;

   use Illuminate\Contracts\Validation\Validator;
   use Illuminate\Foundation\Http\FormRequest;
   use Illuminate\Http\Exceptions\HttpResponseException;

   class StoreUserRequest extends FormRequest
   {
      public function authorize(): bool {
         return $this->user()->hasPermissionTo('create-users');
      }

      public function rules(): array {
         return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8'],
            'avatar'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'bio'           => ['nullable', 'string', 'max:500'],
            'address'       => ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:100'],
            'country'       => ['nullable', 'string', 'max:100'],
            'postal_code'   => ['nullable', 'string', 'max:10'],
            'role'          => ['nullable', 'string', 'exists:roles,name'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
         ];
      }

      public function messages(): array {
         return [
            'name.required'        => 'Nama wajib diisi.',
            'email.required'       => 'Email wajib diisi.',
            'email.unique'         => 'Email sudah terdaftar.',
            'password.required'    => 'Password wajib diisi.',
            'password.min'         => 'Password minimal 8 karakter.',
            'avatar.image'         => 'File harus berupa gambar.',
            'avatar.mimes'         => 'Format gambar: jpeg, png, jpg, gif, webp.',
            'avatar.max'           => 'Ukuran gambar maksimal 2MB.',
            'phone.max'            => 'Nomor telepon maksimal 20 karakter.',
            'bio.max'              => 'Bio maksimal 500 karakter.',
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