<template>
  <div class="mx-auto max-w-2xl space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold">Edit User</h2>
        <p class="text-muted-foreground text-xs">Perbarui data user secara lengkap</p>
      </div>
      <Button variant="outline" size="sm" @click="router.push('/admin/users')">
        <ArrowLeft class="mr-1.5 h-3.5 w-3.5"/>
        Kembali
      </Button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <Loader2 class="h-6 w-6 animate-spin text-primary"/>
      <span class="ml-2 text-sm text-muted-foreground">Memuat data user...</span>
    </div>

    <!-- Error -->
    <div v-else-if="errorMessage" class="text-center py-12">
      <p class="text-destructive text-sm">{{ errorMessage }}</p>
      <Button variant="outline" size="sm" class="mt-4" @click="router.push('/admin/users')">
        Kembali ke Users
      </Button>
    </div>

    <!-- Form -->
    <Card v-else class="border-border/40">
      <CardContent class="p-6">
        <form @submit.prevent="onSubmit" class="space-y-5">
          <!-- Avatar -->
          <div class="flex items-center gap-4">
            <img
              :src="previewAvatar || currentAvatar || 'https://ui-avatars.com/api/?name=User&background=6366f1&color=fff&size=128'"
              alt="Avatar"
              class="h-16 w-16 rounded-full object-cover ring-2 ring-border/40"
            />
            <div class="space-y-1.5">
              <label
                class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-border/40 px-3 py-1.5 text-xs font-medium transition-colors hover:bg-muted/40"
              >
                <Upload class="h-3.5 w-3.5"/>
                Ganti Avatar
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/gif,image/webp"
                  class="hidden"
                  @change="handleAvatarChange"
                />
              </label>
              <button
                v-if="previewAvatar || currentAvatar"
                type="button"
                class="text-destructive text-[10px] hover:underline"
                @click="removeAvatar"
              >
                Hapus Avatar
              </button>
              <p class="text-muted-foreground text-[10px]">Max 2MB (JPEG, PNG, WebP)</p>
            </div>
          </div>

          <!-- Informasi Dasar -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <InputControl
              v-model="form.name"
              id="name"
              label="Nama Lengkap"
              type="text"
              placeholder="John Doe"
              :error="errors.name?.[0]"
              :disabled="formLoading"
              @blur="() => validateField('name')"
            >
              <template #prefix>
                <User class="text-muted-foreground/50 h-3.5 w-3.5"/>
              </template>
            </InputControl>

            <InputControl
              v-model="form.email"
              id="email"
              label="Email"
              type="email"
              placeholder="john@example.com"
              :error="errors.email?.[0]"
              :disabled="formLoading"
              @blur="() => validateField('email')"
            >
              <template #prefix>
                <Mail class="text-muted-foreground/50 h-3.5 w-3.5"/>
              </template>
            </InputControl>

            <InputControl
              v-model="form.phone"
              id="phone"
              label="Nomor Telepon"
              type="text"
              placeholder="08123456789"
              :error="errors.phone?.[0]"
              :disabled="formLoading"
              hint="Opsional"
            >
              <template #prefix>
                <Phone class="text-muted-foreground/50 h-3.5 w-3.5"/>
              </template>
            </InputControl>

            <SelectControl
              v-model="form.role"
              name="role"
              label="Role"
              :options="roleOptions"
              placeholder="Pilih role"
              :error="errors.role?.[0]"
            />
          </div>

          <!-- Password -->
          <InputControl
            v-model="form.password"
            id="password"
            label="Password Baru (opsional)"
            type="password"
            placeholder="••••••••"
            :error="errors.password?.[0]"
            hint="Kosongkan jika tidak ingin mengubah password"
            :disabled="formLoading"
            @blur="() => validateField('password')"
          >
            <template #prefix>
              <Lock class="text-muted-foreground/50 h-3.5 w-3.5"/>
            </template>
          </InputControl>

          <!-- Bio -->
          <TextareaControl
            v-model="form.bio"
            id="bio"
            label="Bio"
            placeholder="Ceritakan tentang user ini"
            :error="errors.bio?.[0]"
            :disabled="formLoading"
            hint="Maksimal 500 karakter"
          />

          <!-- Alamat -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
              <InputControl
                v-model="form.address"
                id="address"
                label="Alamat"
                type="text"
                placeholder="Jl. Contoh No. 123"
                :error="errors.address?.[0]"
                :disabled="formLoading"
              >
                <template #prefix>
                  <MapPin class="text-muted-foreground/50 h-3.5 w-3.5"/>
                </template>
              </InputControl>
            </div>

            <InputControl
              v-model="form.city"
              id="city"
              label="Kota"
              type="text"
              placeholder="Jakarta"
              :error="errors.city?.[0]"
              :disabled="formLoading"
            >
              <template #prefix>
                <Building class="text-muted-foreground/50 h-3.5 w-3.5"/>
              </template>
            </InputControl>

            <InputControl
              v-model="form.postal_code"
              id="postal_code"
              label="Kode Pos"
              type="text"
              placeholder="12345"
              :error="errors.postal_code?.[0]"
              :disabled="formLoading"
            >
              <template #prefix>
                <Hash class="text-muted-foreground/50 h-3.5 w-3.5"/>
              </template>
            </InputControl>

            <div class="md:col-span-2">
              <InputControl
                v-model="form.country"
                id="country"
                label="Negara"
                type="text"
                placeholder="Indonesia"
                :error="errors.country?.[0]"
                :disabled="formLoading"
              >
                <template #prefix>
                  <Globe class="text-muted-foreground/50 h-3.5 w-3.5"/>
                </template>
              </InputControl>
            </div>
          </div>

          <!-- Error -->
          <div
            v-if="errors._general"
            class="bg-destructive/5 border-destructive/20 text-destructive rounded-md border px-4 py-3 text-sm"
          >
            {{ errors._general }}
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-2 border-t border-border/40 pt-4">
            <Button
              type="button"
              variant="outline"
              size="sm"
              :disabled="formLoading"
              @click="router.push('/admin/users')"
            >
              Batal
            </Button>
            <Button type="submit" size="sm" :disabled="formLoading">
              <Loader2 v-if="formLoading" class="mr-1.5 h-3.5 w-3.5 animate-spin"/>
              <Save v-else class="mr-1.5 h-3.5 w-3.5"/>
              {{ formLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import {ref, onMounted} from 'vue'
import {useRouter, useRoute} from 'vue-router'
import * as z from 'zod'
import {
  Loader2, User, Mail, Lock, ArrowLeft,
  Phone, MapPin, Building, Globe, Hash, Upload, Save,
} from 'lucide-vue-next'
import {Button} from '@/components/ui/button'
import InputControl from '@/components/shared/input/input-control.vue'
import SelectControl from '@/components/shared/input/select-control.vue'
import TextareaControl from '@/components/shared/input/textarea-control.vue'
import {Card, CardContent} from '@/components/ui/card'
import {useForm} from '@/composables/useForm'
import {userService} from '@/services/admin/user.service'
import {optionService} from '@/services/admin/option.service'
import type {OptionItem} from '@/types'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const errorMessage = ref<string | null>(null)
const roleOptions = ref<OptionItem[]>([])
const previewAvatar = ref<string | null>(null)
const currentAvatar = ref<string | null>(null)
const avatarFile = ref<File | null>(null)
const avatarRemoved = ref(false) // 🔥 Flag avatar dihapus

const schema = z.object({
  name: z.string().min(1, 'Nama wajib diisi').max(255, 'Nama maksimal 255 karakter'),
  email: z.string().min(1, 'Email wajib diisi').email('Format email tidak valid'),
  phone: z.string().max(20).optional().or(z.literal('')),
  password: z.string().min(8).optional().or(z.literal('')),
  role: z.string().optional(),
  bio: z.string().max(500).optional().or(z.literal('')),
  address: z.string().max(255).optional().or(z.literal('')),
  city: z.string().max(100).optional().or(z.literal('')),
  country: z.string().max(100).optional().or(z.literal('')),
  postal_code: z.string().max(10).optional().or(z.literal('')),
})

const {
  form,
  errors,
  loading: formLoading,
  submit,
  validateField,
  reset,
} = useForm(
  {
    name: '',
    email: '',
    phone: '',
    password: '',
    role: '',
    bio: '',
    address: '',
    city: '',
    country: '',
    postal_code: '',
  },
  {schema, autoFocusError: true}
)

onMounted(async () => {
  try {
    roleOptions.value = await optionService.getRoleOptionsAll()

    const userId = route.params.id as string
    const userData = await userService.getUser(userId)

    if (userData) {
      reset()
      form.name = userData.name || ''
      form.email = userData.email || ''
      form.phone = userData.phone || ''
      form.role = userData.role || ''
      form.bio = userData.bio || ''
      form.address = userData.address || ''
      form.city = userData.city || ''
      form.country = userData.country || ''
      form.postal_code = userData.postal_code || ''
      currentAvatar.value = userData.avatar || null
    } else {
      errorMessage.value = 'User tidak ditemukan.'
    }
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'Gagal memuat data user.'
  } finally {
    loading.value = false
  }
})

const handleAvatarChange = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (input.files && input.files[0]) {
    avatarFile.value = input.files[0]
    previewAvatar.value = URL.createObjectURL(input.files[0])
    avatarRemoved.value = false // Reset flag
  }
}

const removeAvatar = () => {
  avatarFile.value = null
  previewAvatar.value = null
  currentAvatar.value = null
  avatarRemoved.value = true // Set flag hapus
}

const onSubmit = () => {
  console.log('🔵 onSubmit dipanggil')

  submit(
    async (values) => {
      const userId = route.params.id as string

      console.log('📤 Values dari form:', values)
      console.log('   avatarFile:', avatarFile.value)
      console.log('   avatarRemoved:', avatarRemoved.value)

      if (avatarFile.value) {
        // Upload avatar baru
        console.log('📤 Upload avatar baru')
        const formData = new FormData()

        Object.entries(values).forEach(([key, value]) => {
          if (value !== null && value !== undefined && value !== '') {
            formData.append(key, value as any)
          }
        })

        formData.append('avatar', avatarFile.value)
        console.log('📤 FormData entries:')
        formData.forEach((value, key) => {
          console.log(`   ${key}: ${value instanceof File ? value.name : value}`)
        })

        await userService.updateUser(userId, formData)
      } else {
        const payload: any = {
          name: values.name,
          email: values.email,
          phone: values.phone || null,
          bio: values.bio || null,
          address: values.address || null,
          city: values.city || null,
          country: values.country || null,
          postal_code: values.postal_code || null,
        }

        if (values.password) {
          payload.password = values.password
        }

        if (values.role) {
          payload.role = values.role
        }

        if (avatarRemoved.value) {
          console.log('🗑️ Avatar dihapus, kirim remove_avatar')
          payload.remove_avatar = true
        }

        console.log('📤 Payload JSON:', payload)
        await userService.updateUser(userId, payload)
      }
    },
    {
      showSuccessToast: true,
      successMessage: 'User berhasil diperbarui',
      showErrorToast: true,
      onSuccess: (response) => {
        console.log('✅ Update sukses:', response)
        router.push('/admin/users')
      },
      onError: (error) => {
        console.log('❌ Update gagal:', error)
      },
    }
  )
}
</script>