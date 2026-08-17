<?php

   namespace App\Http\Requests\Profile;

   use Illuminate\Contracts\Validation\Validator;
   use Illuminate\Foundation\Http\FormRequest;
   use Illuminate\Http\Exceptions\HttpResponseException;

   class UpdateProfileRequest extends FormRequest
   {
      public function authorize(): bool {
         return true;
      }

      public function rules(): array {
         $userId = auth('api')->id();

         return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'email'       => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'phone'       => ['nullable', 'string', 'max:20'],
            'bio'         => ['nullable', 'string', 'max:500'],
            'address'     => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:100'],
            'country'     => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
         ];
      }

      public function messages(): array {
         return [
            'email.unique'    => 'Email sudah digunakan.',
            'name.max'        => 'Nama maksimal 255 karakter.',
            'phone.max'       => 'Nomor telepon maksimal 20 karakter.',
            'bio.max'         => 'Bio maksimal 500 karakter.',
            'address.max'     => 'Alamat maksimal 255 karakter.',
            'city.max'        => 'Kota maksimal 100 karakter.',
            'country.max'     => 'Negara maksimal 100 karakter.',
            'postal_code.max' => 'Kode pos maksimal 10 karakter.',
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