<?php

   namespace App\Http\Requests\Profile;

   use Illuminate\Contracts\Validation\Validator;
   use Illuminate\Foundation\Http\FormRequest;
   use Illuminate\Http\Exceptions\HttpResponseException;

   class UpdateAvatarRequest extends FormRequest
   {
      public function authorize(): bool {
         return true;
      }

      public function rules(): array {
         return [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
         ];
      }

      public function messages(): array {
         return [
            'avatar.required' => 'File avatar wajib diupload.',
            'avatar.image'    => 'File harus berupa gambar.',
            'avatar.mimes'    => 'Format gambar harus jpeg, png, jpg, gif, atau webp.',
            'avatar.max'      => 'Ukuran gambar maksimal 2MB.',
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