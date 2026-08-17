<?php

   namespace App\Http\Requests\Profile;

   use Illuminate\Contracts\Validation\Validator;
   use Illuminate\Foundation\Http\FormRequest;
   use Illuminate\Http\Exceptions\HttpResponseException;
   use Illuminate\Support\Facades\Hash;

   class UpdatePasswordRequest extends FormRequest
   {
      public function authorize(): bool {
         return true;
      }

      public function rules(): array {
         return [
            'current_password' => ['required', 'string', 'min:8'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
         ];
      }

      public function messages(): array {
         return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.min'      => 'Password saat ini minimal 8 karakter.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.min'          => 'Password baru minimal 8 karakter.',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
            'new_password.different'    => 'Password baru harus berbeda dari password saat ini.',
         ];
      }

      public function withValidator($validator): void {
         $validator->after(function($validator) {
            if(!Hash::check($this->current_password, auth('api')->user()->password)){
               $validator->errors()->add('current_password', 'Password saat ini salah.');
            }
         });
      }

      protected function failedValidation(Validator $validator): void {
         throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors(),
         ], 422));
      }
   }