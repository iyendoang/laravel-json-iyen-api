<?php

   namespace App\Http\Resources;

   use Illuminate\Http\Request;
   use Illuminate\Http\Resources\Json\JsonResource;

   class OptionResource extends JsonResource
   {
      public function toArray(Request $request): array {
         return [
            'label' => $this->getLabel(),
            'value' => $this->getValue(),
            'meta'  => $this->getMeta(),
         ];
      }

      /**
       * Get label untuk dropdown
       */
      private function getLabel(): string {
         if(isset($this->name)){
            return ucwords(str_replace('-', ' ', $this->name));
         }
         if(isset($this->title)){
            return $this->title;
         }

         return (string) ($this->id ?? '');
      }

      /**
       * Get value untuk dropdown
       */
      private function getValue(): string {
         if(isset($this->name) && !isset($this->email)){
            return $this->name;
         }

         return (string) ($this->id ?? '');
      }

      /**
       * Get meta data lengkap
       */
      private function getMeta(): array {
         $meta = [];
         // ID jika ada
         if(isset($this->id)){
            $meta['id'] = $this->id;
         }
         // Name jika ada
         if(isset($this->name)){
            $meta['name'] = $this->name;
         }
         // Email jika ada
         if(isset($this->email)){
            $meta['email'] = $this->email;
         }
         // Guard name jika ada
         if(isset($this->guard_name)){
            $meta['guard_name'] = $this->guard_name;
         }
         // Permissions jika role
         if(isset($this->permissions)){
            $meta['permissions'] = $this->permissions;
         }
         // Created at jika ada
         if(isset($this->created_at)){
            $meta['created_at'] = $this->created_at?->toISOString();
         }
         // Updated at jika ada
         if(isset($this->updated_at)){
            $meta['updated_at'] = $this->updated_at?->toISOString();
         }

         return $meta;
      }
   }