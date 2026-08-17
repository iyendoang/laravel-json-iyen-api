<template>
  <div class="animate-in fade-in zoom-in-95 mx-auto w-full max-w-[340px] duration-500">
    <div class="mb-8 flex items-center justify-center gap-3">
      <div
        class="bg-primary/10 border-primary/20 flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border shadow-sm"
      >
        <img
          v-if="settingStore.appLogo"
          :src="settingStore.appLogo"
          alt="App Logo"
          class="h-full w-full object-contain p-1.5"
        />
        <ShieldCheck v-else class="text-primary h-5 w-5"/>
      </div>
      <div>
        <h1 class="text-foreground text-lg leading-none font-bold tracking-tight">
          {{ settingStore.appName }}
        </h1>
        <p class="text-muted-foreground pt-0.5 text-[11px] font-medium opacity-60">
          Buat Akun Baru
        </p>
      </div>
    </div>

    <Card class="border-border/30 bg-background/50 shadow-sm backdrop-blur-sm">
      <CardHeader class="space-y-1 px-6 pt-6 pb-4">
        <CardTitle class="text-base font-semibold">Register</CardTitle>
        <CardDescription class="text-[12px] leading-snug">
          Daftar untuk mendapatkan akses ke aplikasi.
        </CardDescription>
      </CardHeader>

      <form @submit="onSubmit">
        <CardContent class="grid gap-4 px-6">
          <FormField v-slot="{ componentField }" name="name">
            <FormItem class="space-y-1.5">
              <FormLabel
                class="text-[12px] font-medium"
                :class="nameError ? 'text-destructive' : 'opacity-80'"
              >
                Nama Lengkap
              </FormLabel>
              <FormControl>
                <div class="relative">
                  <User
                    class="absolute top-2.5 left-3 h-3.5 w-3.5"
                    :class="nameError ? 'text-destructive/60' : 'text-muted-foreground/40'"
                  />
                  <Input
                    v-bind="componentField"
                    type="text"
                    placeholder="John Doe"
                    class="h-9 border pl-9 text-[13px] transition-all focus:ring-4"
                    :class="
                                            nameError
                                                ? 'border-destructive focus:border-destructive focus:ring-destructive/10 bg-destructive/5'
                                                : 'bg-muted/5 focus:bg-background border-border/40 focus:border-primary focus:ring-primary/10'
                                        "
                    :disabled="auth.loading"
                  />
                </div>
              </FormControl>
              <FormMessage class="text-[11px] text-destructive"/>
            </FormItem>
          </FormField>

          <FormField v-slot="{ componentField }" name="email">
            <FormItem class="space-y-1.5">
              <FormLabel
                class="text-[12px] font-medium"
                :class="emailError ? 'text-destructive' : 'opacity-80'"
              >
                Email Address
              </FormLabel>
              <FormControl>
                <div class="relative">
                  <Mail
                    class="absolute top-2.5 left-3 h-3.5 w-3.5"
                    :class="emailError ? 'text-destructive/60' : 'text-muted-foreground/40'"
                  />
                  <Input
                    v-bind="componentField"
                    type="email"
                    placeholder="name@enterprise.com"
                    class="h-9 border pl-9 text-[13px] transition-all focus:ring-4"
                    :class="
                                            emailError
                                                ? 'border-destructive focus:border-destructive focus:ring-destructive/10 bg-destructive/5'
                                                : 'bg-muted/5 focus:bg-background border-border/40 focus:border-primary focus:ring-primary/10'
                                        "
                    :disabled="auth.loading"
                  />
                </div>
              </FormControl>
              <FormMessage class="text-[11px] text-destructive"/>
            </FormItem>
          </FormField>

          <FormField v-slot="{ componentField }" name="password">
            <FormItem class="space-y-1.5">
              <FormLabel
                class="text-[12px] font-medium"
                :class="passwordError ? 'text-destructive' : 'opacity-80'"
              >
                Password
              </FormLabel>
              <FormControl>
                <div class="relative">
                  <Lock
                    class="absolute top-2.5 left-3 h-3.5 w-3.5"
                    :class="passwordError ? 'text-destructive/60' : 'text-muted-foreground/40'"
                  />
                  <Input
                    v-bind="componentField"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    class="h-9 px-9 text-[13px] transition-all"
                    :class="
                                            passwordError
                                                ? 'border-destructive focus:border-destructive focus:ring-destructive/10 bg-destructive/5'
                                                : 'bg-muted/5 focus:bg-background border-border/40 focus:border-primary focus:ring-primary/10'
                                        "
                    :disabled="auth.loading"
                  />
                  <button
                    type="button"
                    class="absolute top-2.5 right-3"
                    :class="passwordError ? 'text-destructive/60 hover:text-destructive' : 'text-muted-foreground/40 hover:text-foreground'"
                    @click="showPassword = !showPassword"
                  >
                    <Eye v-if="!showPassword" class="h-3.5 w-3.5"/>
                    <EyeOff v-else class="h-3.5 w-3.5"/>
                  </button>
                </div>
              </FormControl>
              <FormMessage class="text-[11px] text-destructive"/>
            </FormItem>
          </FormField>

          <FormField v-slot="{ componentField }" name="password_confirmation">
            <FormItem class="space-y-1.5">
              <FormLabel
                class="text-[12px] font-medium"
                :class="passwordConfirmationError ? 'text-destructive' : 'opacity-80'"
              >
                Konfirmasi Password
              </FormLabel>
              <FormControl>
                <div class="relative">
                  <Lock
                    class="absolute top-2.5 left-3 h-3.5 w-3.5"
                    :class="passwordConfirmationError ? 'text-destructive/60' : 'text-muted-foreground/40'"
                  />
                  <Input
                    v-bind="componentField"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    class="h-9 px-9 text-[13px] transition-all"
                    :class="
                                            passwordConfirmationError
                                                ? 'border-destructive focus:border-destructive focus:ring-destructive/10 bg-destructive/5'
                                                : 'bg-muted/5 focus:bg-background border-border/40 focus:border-primary focus:ring-primary/10'
                                        "
                    :disabled="auth.loading"
                  />
                </div>
              </FormControl>
              <FormMessage class="text-[11px] text-destructive"/>
            </FormItem>
          </FormField>
        </CardContent>

        <CardFooter class="flex flex-col gap-4 px-6 pt-2 pb-6">
          <Button
            class="h-9 w-full text-[13px] font-medium shadow-sm transition-all active:scale-[0.98]"
            type="submit"
            :disabled="auth.loading"
          >
            <Loader2 v-if="auth.loading" class="mr-2 h-3.5 w-3.5 animate-spin"/>
            {{ auth.loading ? 'Memproses...' : 'Daftar' }}
          </Button>
          <p class="text-muted-foreground text-center text-[10px]">
            Sudah punya akun?
            <router-link to="/login" class="text-primary hover:underline">
              Login
            </router-link>
          </p>
        </CardFooter>
      </form>
    </Card>
  </div>
</template>

<script setup lang="ts">
import {ref} from 'vue'
import {useRouter} from 'vue-router'
import {useForm, useField} from 'vee-validate'
import {toTypedSchema} from '@vee-validate/zod'
import * as z from 'zod'

import {useAuthStore} from '@/stores/auth-store'
import {useSettingStore} from '@/stores/setting-store'
import {Button} from '@/components/ui/button'
import {Input} from '@/components/ui/input'
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
import {Eye, EyeOff, Loader2, Lock, Mail, ShieldCheck, User} from 'lucide-vue-next'
import {toast} from 'vue-sonner'
import type {RegisterData} from '@/types'

const router = useRouter()
const auth = useAuthStore()
const settingStore = useSettingStore()
const showPassword = ref(false)

const formSchema = toTypedSchema(
  z.object({
    name: z.string().min(1, 'Nama wajib diisi').max(255, 'Nama maksimal 255 karakter'),
    email: z.string().min(1, 'Email wajib diisi').email('Format email tidak valid'),
    password: z.string().min(8, 'Password minimal 8 karakter'),
    password_confirmation: z.string().min(8, 'Konfirmasi password minimal 8 karakter'),
  }).refine((data) => data.password === data.password_confirmation, {
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

// Gunakan useField untuk mendapatkan error state
const {errorMessage: nameError} = useField('name')
const {errorMessage: emailError} = useField('email')
const {errorMessage: passwordError} = useField('password')
const {errorMessage: passwordConfirmationError} = useField('password_confirmation')

const onSubmit = form.handleSubmit(async (values: RegisterData) => {
  try {
    const success = await auth.register(values)
    if (success) {
      toast.success('Registrasi berhasil! Selamat datang.')
      router.push('/admin')
    } else {
      toast.error(auth.error || 'Registrasi gagal')
    }
  } catch (error: any) {
    console.error('Register error:', error)
    toast.error(error?.response?.data?.message || 'Terjadi kesalahan saat registrasi')
  }
})
</script>