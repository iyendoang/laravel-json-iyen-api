<template>
  <div class="mx-auto max-w-2xl space-y-6">
    <!-- Header -->
    <div>
      <h2 class="text-xl font-bold">Profile</h2>
      <p class="text-muted-foreground text-xs">Kelola informasi profil Anda</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <Loader2 class="h-6 w-6 animate-spin text-primary" />
      <span class="ml-2 text-sm text-muted-foreground">Memuat profile...</span>
    </div>

    <!-- Profile Form -->
    <Card v-else class="border-border/40">
      <CardContent class="p-6 space-y-6">
        <!-- Avatar Section -->
        <div class="flex items-center gap-4 pb-4 border-b border-border/40">
          <img
            :src="previewAvatar || (authStore.user?.avatar as string) || 'https://ui-avatars.com/api/?name=User&background=6366f1&color=fff&size=128'"
            alt="Avatar"
            class="h-16 w-16 rounded-full object-cover ring-2 ring-border/40"
          />
          <div class="space-y-1.5">
            <label
              class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-border/40 px-3 py-1.5 text-xs font-medium transition-colors hover:bg-muted/40"
            >
              <Upload class="h-3.5 w-3.5" />
              Ganti Avatar
              <input
                type="file"
                accept="image/jpeg,image/png,image/gif,image/webp"
                class="hidden"
                @change="handleAvatarChange"
              />
            </label>
            <button
              v-if="previewAvatar || authStore.user?.avatar"
              type="button"
              class="text-destructive text-[10px] hover:underline"
              @click="handleDeleteAvatar"
            >
              Hapus Avatar
            </button>
            <p class="text-muted-foreground text-[10px]">Max 2MB (JPEG, PNG, WebP)</p>
          </div>
        </div>

        <!-- Profile Form -->
        <form @submit.prevent="onSubmit" class="space-y-5">
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
                <UserIcon class="text-muted-foreground/50 h-3.5 w-3.5" />
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
                <Mail class="text-muted-foreground/50 h-3.5 w-3.5" />
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
                <Phone class="text-muted-foreground/50 h-3.5 w-3.5" />
              </template>
            </InputControl>

            <InputControl
              v-model="form.role"
              id="role"
              label="Role"
              type="text"
              :disabled="true"
              hint="Role tidak dapat diubah"
            >
              <template #prefix>
                <Shield class="text-muted-foreground/50 h-3.5 w-3.5" />
              </template>
            </InputControl>
          </div>

          <!-- Bio -->
          <TextareaControl
            v-model="form.bio"
            id="bio"
            label="Bio"
            placeholder="Ceritakan tentang diri Anda"
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
                  <MapPin class="text-muted-foreground/50 h-3.5 w-3.5" />
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
                <Building class="text-muted-foreground/50 h-3.5 w-3.5" />
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
                <Hash class="text-muted-foreground/50 h-3.5 w-3.5" />
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
                  <Globe class="text-muted-foreground/50 h-3.5 w-3.5" />
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
            <Button type="submit" size="sm" :disabled="formLoading">
              <Loader2 v-if="formLoading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
              <Save v-else class="mr-1.5 h-3.5 w-3.5" />
              {{ formLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>

    <!-- Password Card -->
    <Card class="border-border/40">
      <CardContent class="p-6">
        <h3 class="text-sm font-semibold mb-4">Ganti Password</h3>
        <form @submit.prevent="onPasswordSubmit" class="space-y-4">
          <InputControl
            v-model="passwordForm.current_password"
            id="current_password"
            label="Password Saat Ini"
            type="password"
            placeholder="••••••••"
            :error="passwordErrors.current_password?.[0]"
            :disabled="passwordLoading"
          >
            <template #prefix>
              <Lock class="text-muted-foreground/50 h-3.5 w-3.5" />
            </template>
          </InputControl>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <InputControl
              v-model="passwordForm.new_password"
              id="new_password"
              label="Password Baru"
              type="password"
              placeholder="••••••••"
              :error="passwordErrors.new_password?.[0]"
              :disabled="passwordLoading"
            >
              <template #prefix>
                <Lock class="text-muted-foreground/50 h-3.5 w-3.5" />
              </template>
            </InputControl>

            <InputControl
              v-model="passwordForm.new_password_confirmation"
              id="new_password_confirmation"
              label="Konfirmasi Password Baru"
              type="password"
              placeholder="••••••••"
              :error="passwordErrors.new_password_confirmation?.[0]"
              :disabled="passwordLoading"
            >
              <template #prefix>
                <Lock class="text-muted-foreground/50 h-3.5 w-3.5" />
              </template>
            </InputControl>
          </div>

          <div class="flex justify-end">
            <Button type="submit" size="sm" :disabled="passwordLoading">
              <Loader2 v-if="passwordLoading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
              {{ passwordLoading ? 'Menyimpan...' : 'Ganti Password' }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import * as z from 'zod'
import {
  Loader2, User as UserIcon, Mail, Lock, Phone,
  MapPin, Building, Globe, Hash, Upload, Save, Shield,
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import InputControl from '@/components/shared/input/input-control.vue'
import TextareaControl from '@/components/shared/input/textarea-control.vue'
import { Card, CardContent } from '@/components/ui/card'
import { useForm } from '@/composables/useForm'
import { profileService } from '@/services/profile.service'
import { useAuthStore } from '@/stores/auth-store'

const authStore = useAuthStore()

const loading = ref(true)
const previewAvatar = ref<string | null>(null)
const avatarFile = ref<File | null>(null)

const profileSchema = z.object({
  name: z.string().min(1, 'Nama wajib diisi').max(255),
  email: z.string().min(1, 'Email wajib diisi').email('Format email tidak valid'),
  phone: z.string().max(20).optional().or(z.literal('')),
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
    role: '',
    bio: '',
    address: '',
    city: '',
    country: '',
    postal_code: '',
  },
  { schema: profileSchema, autoFocusError: true }
)

const passwordSchema = z.object({
  current_password: z.string().min(8, 'Password saat ini minimal 8 karakter'),
  new_password: z.string().min(8, 'Password baru minimal 8 karakter'),
  new_password_confirmation: z.string().min(8, 'Konfirmasi password minimal 8 karakter'),
}).refine((data) => data.new_password === data.new_password_confirmation, {
  message: 'Konfirmasi password tidak cocok',
  path: ['new_password_confirmation'],
})

const {
  form: passwordForm,
  errors: passwordErrors,
  loading: passwordLoading,
  submit: passwordSubmit,
} = useForm(
  {
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
  },
  { schema: passwordSchema, autoFocusError: true }
)

onMounted(async () => {
  try {
    const userData = await profileService.getProfile()
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

    authStore.user = userData
  } catch (error) {
    console.error('Gagal memuat profile:', error)
  } finally {
    loading.value = false
  }
})

const handleAvatarChange = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (input.files && input.files[0]) {
    avatarFile.value = input.files[0]
    previewAvatar.value = URL.createObjectURL(input.files[0])

    profileService.updateAvatar(input.files[0]).then((user) => {
      authStore.user = user
      previewAvatar.value = null
      avatarFile.value = null
    })
  }
}

const handleDeleteAvatar = async () => {
  try {
    const user = await profileService.deleteAvatar()
    authStore.user = user
  } catch (error) {
    console.error('Gagal hapus avatar:', error)
  }
}

const onSubmit = () => {
  submit(
    async (values) => {
      const payload = {
        name: values.name,
        email: values.email,
        phone: values.phone || null,
        bio: values.bio || null,
        address: values.address || null,
        city: values.city || null,
        country: values.country || null,
        postal_code: values.postal_code || null,
      }

      const user = await profileService.updateProfile(payload)
      authStore.user = user
    },
    {
      showSuccessToast: true,
      successMessage: 'Profile berhasil diperbarui',
      showErrorToast: true,
    }
  )
}

const onPasswordSubmit = () => {
  passwordSubmit(
    async (values) => {
      await profileService.updatePassword(values)
      passwordForm.current_password = ''
      passwordForm.new_password = ''
      passwordForm.new_password_confirmation = ''
    },
    {
      showSuccessToast: true,
      successMessage: 'Password berhasil diperbarui',
      showErrorToast: true,
    }
  )
}
</script>