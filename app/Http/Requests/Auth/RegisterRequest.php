<?php

   namespace App\Http\Requests\Auth;

   use Illuminate\Contracts\Validation\Validator;
   use Illuminate\Foundation\Http\FormRequest;
   use Illuminate\Http\Exceptions\HttpResponseException;

   class RegisterRequest extends FormRequest
   {
      public function authorize(): bool {
         return true;
      }

      public function rules(): array {
         return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
         ];
      }

      public function messages(): array {
         return [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
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