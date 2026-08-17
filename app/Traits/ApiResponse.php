<?php

   namespace App\Traits;

   use Illuminate\Http\JsonResponse;
   use Illuminate\Http\Resources\Json\JsonResource;
   use Illuminate\Http\Resources\Json\ResourceCollection;

   trait ApiResponse
   {
      /**
       * Respons sukses standar
       */
      protected function responseSuccess(string $message, mixed $data = NULL, int $code = 200): JsonResponse {
         $response = [
            'status'  => 'success',
            'message' => $message,
         ];
         if($data !== NULL){
            $response['data'] = $data;
         }

         return response()->json($response, $code);
      }

      /**
       * Respons error standar
       */
      protected function responseError(string $message, int $code = 400, array $errors = []): JsonResponse {
         $response = [
            'status'  => 'error',
            'message' => $message,
         ];
         if(!empty($errors)){
            $response['errors'] = $errors;
         }

         return response()->json($response, $code);
      }

      /**
       * Respons untuk single resource
       */
      protected function responseResource(JsonResource $resource, string $message = 'Data berhasil diambil', int $code = 200): JsonResponse {
         return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $resource,
         ], $code);
      }

      /**
       * Respons untuk collection resource (tanpa pagination)
       */
      protected function responseCollection(ResourceCollection $collection, string $message = 'Data berhasil diambil', int $code = 200): JsonResponse {
         return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $collection,
         ], $code);
      }

      /**
       * Respons untuk data dengan paginasi
       * Mendukung JsonResource::collection() secara otomatis
       */
      protected function responsePaginate(ResourceCollection $resource, string $message = 'Data berhasil diambil', int $code = 200): JsonResponse {
         $data = $resource->response()->getData(true);

         return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data['data'],
            'meta'    => $data['meta'] ?? NULL,
            'links'   => $data['links'] ?? NULL,
         ], $code);
      }
   }