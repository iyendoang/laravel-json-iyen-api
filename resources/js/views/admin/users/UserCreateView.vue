<template>
  <div class="mx-auto max-w-3xl space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold">Tambah User</h2>
        <p class="text-muted-foreground text-xs">Buat user baru untuk sistem</p>
      </div>
      <Button variant="outline" size="sm" @click="router.push('/admin/users')">
        <ArrowLeft class="mr-1.5 h-3.5 w-3.5"/>
        Kembali
      </Button>
    </div>

    <!-- Form Card -->
    <Card class="border-border/40">
      <CardContent class="p-6">
        <form @submit.prevent="onSubmit" class="space-y-8">
          <!-- Section: Avatar -->
          <section>
            <h3 class="text-sm font-semibold mb-4 flex items-center gap-2">
              <UserCircle class="text-primary h-4 w-4"/>
              Avatar
            </h3>
            <div class="flex items-center gap-6">
              <!-- Avatar Preview -->
              <div class="relative">
                <img
                  :src="previewAvatar || 'https://ui-avatars.com/api/?name=User&background=6366f1&color=fff&size=128'"
                  alt="Avatar Preview"
                  class="h-20 w-20 rounded-full object-cover ring-2 ring-border/40"
                />
                <button
                  v-if="previewAvatar"
                  type="button"
                  class="bg-destructive absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full text-white shadow-sm hover:opacity-90"
                  @click="removeAvatar"
                >
                  <X class="h-3 w-3"/>
                </button>
              </div>

              <!-- Upload Controls -->
              <div class="space-y-2">
                <label
                  class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-border/40 px-3 py-1.5 text-xs font-medium transition-colors hover:bg-muted/40"
                >
                  <Upload class="h-3.5 w-3.5"/>
                  Upload Avatar
                  <input
                    type="file"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    class="hidden"
                    @change="handleAvatarChange"
                  />
                </label>
                <p class="text-muted-foreground text-[10px]">
                  Max 2MB (JPEG, PNG, GIF, WebP)
                </p>
              </div>
            </div>
          </section>

          <!-- Divider -->
          <div class="border-t border-border/40"></div>

          <!-- Section: Informasi Dasar -->
          <section>
            <h3 class="text-sm font-semibold mb-4 flex items-center gap-2">
              <Info class="text-primary h-4 w-4"/>
              Informasi Dasar
            </h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <InputControl
                v-model="form.name"
                id="name"
                label="Nama Lengkap"
                type="text"
                placeholder="John Doe"
                :error="errors.name?.[0]"
                :disabled="formLoading"
                hint="Nama lengkap user"
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
                hint="Email aktif untuk login"
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
                hint="Role menentukan permission user"
              />
            </div>
          </section>

          <!-- Divider -->
          <div class="border-t border-border/40"></div>

          <!-- Section: Password -->
          <section>
            <h3 class="text-sm font-semibold mb-4 flex items-center gap-2">
              <Lock class="text-primary h-4 w-4"/>
              Password
            </h3>
            <InputControl
              v-model="form.password"
              id="password"
              label="Password"
              type="password"
              placeholder="••••••••"
              :error="errors.password?.[0]"
              hint="Minimal 8 karakter"
              :disabled="formLoading"
            >
              <template #prefix>
                <Lock class="text-muted-foreground/50 h-3.5 w-3.5"/>
              </template>
            </InputControl>
          </section>

          <!-- Divider -->
          <div class="border-t border-border/40"></div>

          <!-- Section: Bio -->
          <section>
            <h3 class="text-sm font-semibold mb-4 flex items-center gap-2">
              <FileText class="text-primary h-4 w-4"/>
              Bio
            </h3>
            <InputControl
              v-model="form.bio"
              id="bio"
              label="Bio"
              type="text"
              placeholder="Ceritakan tentang user ini"
              :error="errors.bio?.[0]"
              :disabled="formLoading"
              hint="Maksimal 500 karakter"
            >
              <template #prefix>
                <FileText class="text-muted-foreground/50 h-3.5 w-3.5"/>
              </template>
            </InputControl>
          </section>

          <!-- Divider -->
          <div class="border-t border-border/40"></div>

          <!-- Section: Alamat -->
          <section>
            <h3 class="text-sm font-semibold mb-4 flex items-center gap-2">
              <MapPin class="text-primary h-4 w-4"/>
              Alamat
            </h3>
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
          </section>

          <!-- Error General -->
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
              <UserPlus v-else class="mr-1.5 h-3.5 w-3.5"/>
              {{ formLoading ? 'Menyimpan...' : 'Simpan User' }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import {ref, onMounted} from 'vue'
import {useRouter} from 'vue-router'
import * as z from 'zod'
import {
  Loader2, User, Mail, Lock, ArrowLeft,
  Phone, FileText, MapPin, Building, Globe, Hash,
  UserCircle, Info, Upload, X, UserPlus,
} from 'lucide-vue-next'
import {Button} from '@/components/ui/button'
import InputControl from '@/components/shared/input/input-control.vue'
import SelectControl from '@/components/shared/input/select-control.vue'
import {Card, CardContent} from '@/components/ui/card'
import {useForm} from '@/composables/useForm'
import {userService} from '@/services/admin/user.service'
import {optionService} from '@/services/admin/option.service'
import type {OptionItem} from '@/types'

const router = useRouter()
const roleOptions = ref<OptionItem[]>([])
const previewAvatar = ref<string | null>(null)
const avatarFile = ref<File | null>(null)

const schema = z.object({
  name: z.string().min(1, 'Nama wajib diisi').max(255, 'Nama maksimal 255 karakter'),
  email: z.string().min(1, 'Email wajib diisi').email('Format email tidak valid'),
  phone: z.string().max(20).optional().or(z.literal('')),
  password: z.string().min(8, 'Password minimal 8 karakter'),
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
  roleOptions.value = await optionService.getRoleOptionsAll()
})

const handleAvatarChange = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (input.files && input.files[0]) {
    avatarFile.value = input.files[0]
    previewAvatar.value = URL.createObjectURL(input.files[0])
  }
}

const removeAvatar = () => {
  avatarFile.value = null
  previewAvatar.value = null
}

const onSubmit = () => {
  submit(
    async (values) => {
      if (avatarFile.value) {
        const formData = new FormData()
        Object.entries(values).forEach(([key, value]) => {
          if (value !== null && value !== undefined && value !== '') {
            formData.append(key, value as any)
          }
        })
        formData.append('avatar', avatarFile.value)
        await userService.createUser(formData)
      } else {
        await userService.createUser(values as any)
      }
    },
    {
      showSuccessToast: true,
      successMessage: 'User berhasil dibuat',
      showErrorToast: true,
      onSuccess: () => {
        router.push('/admin/users')
      },
    }
  )
}
</script>