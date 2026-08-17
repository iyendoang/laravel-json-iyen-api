<?php

   namespace App\Http\Requests\Permission;

   use Illuminate\Contracts\Validation\Validator;
   use Illuminate\Foundation\Http\FormRequest;
   use Illuminate\Http\Exceptions\HttpResponseException;

   class StorePermissionRequest extends FormRequest
   {
      public function authorize(): bool {
         return auth('api')->user()->hasPermissionTo('create-permissions');
      }

      public function rules(): array {
         return [
            'name' => [
               'required',
               'string',
               'max:255',
               'unique:permissions,name',
               'regex:/^[a-z0-9-]+$/',
            ],
         ];
      }

      public function messages(): array {
         return [
            'name.required' => 'Nama permission wajib diisi.',
            'name.unique'   => 'Nama permission sudah digunakan.',
            'name.regex'    => 'Nama permission hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
         ];
      }

      protected function prepareForValidation(): void {
         if($this->has('name')){
            $this->merge([
               'name' => strtolower(trim($this->name)),
            ]);
         }
      }

      protected function failedValidation(Validator $validator): void {
         throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors(),
         ], 422));
      }
   }