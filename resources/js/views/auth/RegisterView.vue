<template>
  <div class="w-full max-w-[380px] mx-auto space-y-6 animate-in fade-in zoom-in-95 duration-300">
    <!-- Header Brand & Back to Home -->
    <div class="space-y-4 text-center">
      <router-link
        to="/"
        class="inline-flex items-center gap-1.5 text-xs text-muted-foreground hover:text-foreground transition-colors"
      >
        <ArrowLeft class="h-3.5 w-3.5" />
        Kembali ke Halaman Depan
      </router-link>

      <div class="flex flex-col items-center justify-center gap-2">
        <router-link to="/" class="group">
          <div class="h-12 w-12 rounded-2xl border border-primary/20 bg-primary/10 p-2 flex items-center justify-center shadow-xs transition-transform group-hover:scale-105">
            <img
              v-if="settingStore.appLogo"
              :src="settingStore.appLogo"
              :alt="settingStore.appName"
              class="h-full w-full object-contain"
            />
            <ShieldCheck v-else class="text-primary h-6 w-6" />
          </div>
        </router-link>

        <div>
          <h1 class="text-foreground text-xl font-bold tracking-tight">
            {{ settingStore.appName }}
          </h1>
          <p class="text-muted-foreground text-xs">
            {{ settingStore.companyInfo.tagline || settingStore.appSlogan || 'Buat Akun Baru' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Register Card -->
    <Card class="border-border/60 bg-card/80 shadow-md backdrop-blur-md">
      <CardHeader class="space-y-1 pb-4">
        <CardTitle class="text-base font-semibold text-foreground">Daftar Akun</CardTitle>
        <CardDescription class="text-xs">
          Lengkapi formulir di bawah untuk mendaftar akun baru.
        </CardDescription>
      </CardHeader>

      <form @submit="onSubmit">
        <CardContent class="space-y-4">
          <!-- Backend Error Banner -->
          <div
            v-if="auth.error"
            class="flex items-start gap-2.5 rounded-lg border border-destructive/20 bg-destructive/10 p-3 text-xs text-destructive"
          >
            <AlertCircle class="h-4 w-4 shrink-0 mt-0.5" />
            <span>{{ auth.error }}</span>
          </div>

          <!-- Name Input -->
          <FormField v-slot="{ componentField }" name="name">
            <FormItem class="space-y-1.5">
              <FormLabel class="text-xs font-medium opacity-90">Nama Lengkap</FormLabel>
              <FormControl>
                <div class="relative">
                  <User class="absolute top-2.5 left-3 h-4 w-4 text-muted-foreground/50" />
                  <Input
                    v-bind="componentField"
                    type="text"
                    placeholder="Nama Lengkap"
                    class="h-9 pl-9 text-xs transition-all bg-background/50 focus:bg-background"
                    :class="nameError ? 'border-destructive focus-visible:ring-destructive/20' : 'border-border/60'"
                    :disabled="auth.loading"
                  />
                </div>
              </FormControl>
              <FormMessage class="text-[11px] text-destructive" />
            </FormItem>
          </FormField>

          <!-- Email Input -->
          <FormField v-slot="{ componentField }" name="email">
            <FormItem class="space-y-1.5">
              <FormLabel class="text-xs font-medium opacity-90">Email Address</FormLabel>
              <FormControl>
                <div class="relative">
                  <Mail class="absolute top-2.5 left-3 h-4 w-4 text-muted-foreground/50" />
                  <Input
                    v-bind="componentField"
                    type="email"
                    placeholder="nama@perusahaan.com"
                    class="h-9 pl-9 text-xs transition-all bg-background/50 focus:bg-background"
                    :class="emailError ? 'border-destructive focus-visible:ring-destructive/20' : 'border-border/60'"
                    :disabled="auth.loading"
                  />
                </div>
              </FormControl>
              <FormMessage class="text-[11px] text-destructive" />
            </FormItem>
          </FormField>

          <!-- Password Input -->
          <FormField v-slot="{ componentField }" name="password">
            <FormItem class="space-y-1.5">
              <FormLabel class="text-xs font-medium opacity-90">Password</FormLabel>
              <FormControl>
                <div class="relative">
                  <Lock class="absolute top-2.5 left-3 h-4 w-4 text-muted-foreground/50" />
                  <Input
                    v-bind="componentField"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    class="h-9 px-9 text-xs transition-all bg-background/50 focus:bg-background"
                    :class="passwordError ? 'border-destructive focus-visible:ring-destructive/20' : 'border-border/60'"
                    :disabled="auth.loading"
                  />
                  <button
                    type="button"
                    tabindex="-1"
                    class="absolute top-2.5 right-3 text-muted-foreground/50 hover:text-foreground transition-colors"
                    @click="showPassword = !showPassword"
                  >
                    <Eye v-if="!showPassword" class="h-4 w-4" />
                    <EyeOff v-else class="h-4 w-4" />
                  </button>
                </div>
              </FormControl>
              <FormMessage class="text-[11px] text-destructive" />
            </FormItem>
          </FormField>

          <!-- Password Confirmation Input -->
          <FormField v-slot="{ componentField }" name="password_confirmation">
            <FormItem class="space-y-1.5">
              <FormLabel class="text-xs font-medium opacity-90">Konfirmasi Password</FormLabel>
              <FormControl>
                <div class="relative">
                  <Lock class="absolute top-2.5 left-3 h-4 w-4 text-muted-foreground/50" />
                  <Input
                    v-bind="componentField"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    class="h-9 px-9 text-xs transition-all bg-background/50 focus:bg-background"
                    :class="passwordConfirmationError ? 'border-destructive focus-visible:ring-destructive/20' : 'border-border/60'"
                    :disabled="auth.loading"
                  />
                </div>
              </FormControl>
              <FormMessage class="text-[11px] text-destructive" />
            </FormItem>
          </FormField>
        </CardContent>

        <CardFooter class="flex flex-col gap-4 pt-6 pb-6">
          <Button
            type="submit"
            class="h-9 w-full text-xs font-semibold shadow-sm transition-all active:scale-[0.98]"
            :disabled="auth.loading"
          >
            <Loader2 v-if="auth.loading" class="mr-2 h-3.5 w-3.5 animate-spin" />
            {{ auth.loading ? 'Mendaftarkan Akun...' : 'Daftar Sekarang' }}
          </Button>

          <p class="text-center text-xs text-muted-foreground">
            Sudah memiliki akun?
            <router-link to="/login" class="font-semibold text-primary hover:underline">
              Masuk di sini
            </router-link>
          </p>
        </CardFooter>
      </form>
    </Card>

    <!-- Footer Simple Copyright -->
    <p class="text-center text-[11px] text-muted-foreground/70">
      {{ settingStore.footerText }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useForm, useField } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import * as z from 'zod'

import { useAuthStore } from '@/stores/auth-store'
import { useSettingStore } from '@/stores/setting-store'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
import {
  Eye,
  EyeOff,
  Loader2,
  Lock,
  Mail,
  ShieldCheck,
  User,
  ArrowLeft,
  AlertCircle,
} from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import type { RegisterData } from '@/types'

const router = useRouter()
const auth = useAuthStore()
const settingStore = useSettingStore()
const showPassword = ref(false)

const formSchema = toTypedSchema(
  z
    .object({
      name: z.string().min(1, 'Nama wajib diisi').max(255, 'Nama maksimal 255 karakter'),
      email: z.string().min(1, 'Email wajib diisi').email('Format email tidak valid'),
      password: z.string().min(8, 'Password minimal 8 karakter'),
      password_confirmation: z.string().min(8, 'Konfirmasi password minimal 8 karakter'),
    })
    .refine((data) => data.password === data.password_confirmation, {
      message: 'Konfirmasi password tidak cocok',
      path: ['password_confirmation'],
    })
)

const form = useForm({
  validationSchema: formSchema,
  initialValues: {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  },
})

const { errorMessage: nameError } = useField('name')
const { errorMessage: emailError } = useField('email')
const { errorMessage: passwordError } = useField('password')
const { errorMessage: passwordConfirmationError } = useField('password_confirmation')

const onSubmit = form.handleSubmit(async (values: RegisterData) => {
  try {
    const success = await auth.register(values)
    if (success) {
      toast.success('Registrasi berhasil! Selamat datang.')
      await router.push('/admin')
    } else {
      toast.error(auth.error || 'Registrasi gagal')
    }
  } catch (error: any) {
    console.error('Register error:', error)
    toast.error(error?.response?.data?.message || 'Terjadi kesalahan saat registrasi')
  }
})
</script>