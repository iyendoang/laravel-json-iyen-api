<template>
  <div class="mx-auto max-w-4xl space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-2xl font-bold tracking-tight">Pengaturan Sistem & Perusahaan</h2>
        <p class="text-muted-foreground text-xs">Kelola profil bisnis, visual aset, banner hero, kontak, dan tautan sosial media.</p>
      </div>
      <Button
        type="button"
        size="sm"
        :disabled="formLoading || loading"
        @click="onSubmit"
      >
        <Loader2 v-if="formLoading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
        <Save v-else class="mr-1.5 h-3.5 w-3.5" />
        {{ formLoading ? 'Menyimpan...' : 'Simpan Semua Pengaturan' }}
      </Button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-3">
      <Loader2 class="h-8 w-8 animate-spin text-primary" />
      <span class="text-sm text-muted-foreground font-medium">Memuat data pengaturan...</span>
    </div>

    <!-- Form Utama -->
    <form v-else @submit.prevent="onSubmit" class="space-y-6">
      <!-- Error General Notification -->
      <div
        v-if="errors._general"
        class="bg-destructive/10 border-destructive/20 text-destructive rounded-lg border p-4 text-sm flex items-center gap-2"
      >
        <AlertCircle class="h-4 w-4 shrink-0" />
        <span>{{ errors._general }}</span>
      </div>

      <!-- Tab Navigation -->
      <div class="flex border-b border-border/60 gap-2 overflow-x-auto pb-px">
        <button
          type="button"
          v-for="tab in tabList"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="flex items-center gap-2 px-4 py-2.5 text-xs font-medium border-b-2 transition-all whitespace-nowrap relative"
          :class="[
            activeTab === tab.id
              ? 'border-primary text-primary font-semibold'
              : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted-foreground/30'
          ]"
        >
          <component :is="tab.icon" class="h-4 w-4" />
          {{ tab.label }}
          <!-- Error Badge Indicator -->
          <span
            v-if="hasTabErrors(tab.id)"
            class="h-2 w-2 rounded-full bg-destructive animate-pulse"
          />
        </button>
      </div>

      <!-- TAB 1: Brand & Media -->
      <div v-show="activeTab === 'brand'" class="space-y-6">
        <Card class="border-border/50 shadow-sm">
          <CardHeader class="pb-3">
            <CardTitle class="text-sm font-semibold">Identitas Aplikasi & Visual Brand</CardTitle>
            <CardDescription class="text-xs">Pengaturan logo, favicon, banner hero, dan nama aplikasi utama.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Upload Group (Logo & Favicon) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 rounded-lg bg-muted/20 border border-border/40">
              <!-- Logo Upload -->
              <div class="flex items-start gap-4">
                <img
                  :src="previewFiles.app_logo || form.app_logo || 'https://ui-avatars.com/api/?name=App&background=6366f1&color=fff&size=128'"
                  alt="App Logo"
                  class="h-16 w-16 rounded-lg object-contain ring-1 ring-border/50 bg-background p-1"
                />
                <div class="space-y-1.5 flex-1">
                  <span class="text-xs font-medium block">Logo Aplikasi</span>
                  <label
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-border/60 bg-background px-3 py-1.5 text-xs font-medium shadow-xs hover:bg-muted/50"
                  >
                    <Upload class="h-3.5 w-3.5" />
                    Pilih Logo
                    <input
                      type="file"
                      accept="image/jpeg,image/png,image/webp,image/svg+xml"
                      class="hidden"
                      @change="(e) => handleFileChange(e, 'app_logo')"
                    />
                  </label>
                  <p class="text-muted-foreground text-[10px]">Format: PNG, WebP, JPG (Maks. 2MB)</p>
                </div>
              </div>

              <!-- Favicon Upload -->
              <div class="flex items-start gap-4">
                <img
                  :src="previewFiles.app_favicon || form.app_favicon || 'https://ui-avatars.com/api/?name=Fav&background=10b981&color=fff&size=64'"
                  alt="Favicon"
                  class="h-16 w-16 rounded-lg object-contain ring-1 ring-border/50 bg-background p-2"
                />
                <div class="space-y-1.5 flex-1">
                  <span class="text-xs font-medium block">Favicon Browser</span>
                  <label
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-border/60 bg-background px-3 py-1.5 text-xs font-medium shadow-xs hover:bg-muted/50"
                  >
                    <Upload class="h-3.5 w-3.5" />
                    Pilih Favicon
                    <input
                      type="file"
                      accept="image/png,image/x-icon,image/webp"
                      class="hidden"
                      @change="(e) => handleFileChange(e, 'app_favicon')"
                    />
                  </label>
                  <p class="text-muted-foreground text-[10px]">Ukuran rekomendasi: 32x32 atau 64x64 px</p>
                </div>
              </div>
            </div>

            <!-- Hero Image Banner Upload -->
            <div class="p-4 rounded-lg bg-muted/20 border border-border/40 space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-xs font-semibold block">Gambar Banner Hero (Opsional)</span>
                  <p class="text-muted-foreground text-[11px]">Gambar ilustrasi/foto besar yang tampil pada header Landing Page.</p>
                </div>
                <label
                  class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-border/60 bg-background px-3 py-1.5 text-xs font-medium shadow-xs hover:bg-muted/50"
                >
                  <Upload class="h-3.5 w-3.5" />
                  Pilih Banner Hero
                  <input
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    @change="(e) => handleFileChange(e, 'hero_image')"
                  />
                </label>
              </div>

              <!-- Preview Banner Hero -->
              <div v-if="previewFiles.hero_image || form.hero_image" class="relative rounded-lg overflow-hidden border border-border/50 bg-background/50 h-44 flex items-center justify-center">
                <img
                  :src="previewFiles.hero_image || form.hero_image"
                  alt="Hero Banner Preview"
                  class="w-full h-full object-cover"
                />
              </div>
            </div>

            <!-- Identitas Teks -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <InputControl
                v-model="form.app_name"
                id="app_name"
                label="Nama Aplikasi / Sistem"
                type="text"
                placeholder="Laravel REST API"
                :error="errors.app_name?.[0]"
                :disabled="formLoading"
                @blur="() => validateField('app_name')"
              />

              <InputControl
                v-model="form.app_slogan"
                id="app_slogan"
                label="Slogan Singkat"
                type="text"
                placeholder="Modern Enterprise API"
                :error="errors.app_slogan?.[0]"
                :disabled="formLoading"
              />
            </div>

            <div>
              <TextareaControl
                v-model="form.app_description"
                id="app_description"
                label="Ringkasan / Deskripsi Aplikasi"
                placeholder="Penjelasan umum tentang sistem..."
                :rows="3"
                :error="errors.app_description?.[0]"
                :disabled="formLoading"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- TAB 2: Hero & Landing Page Content -->
      <div v-show="activeTab === 'hero'" class="space-y-6">
        <Card class="border-border/50 shadow-sm">
          <CardHeader class="pb-3">
            <CardTitle class="text-sm font-semibold">Konten Hero Landing Page</CardTitle>
            <CardDescription class="text-xs">Kustomisasi teks headline, sub-headline, dan tombol Call to Action (CTA) di bagian atas beranda.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <InputControl
                v-model="form.hero_badge"
                id="hero_badge"
                label="Teks Badge / Tagline Atas"
                type="text"
                placeholder="🔥 Solusi Transformasi Digital"
                :disabled="formLoading"
              />

              <InputControl
                v-model="form.hero_title"
                id="hero_title"
                label="Judul Utama Hero (Headline)"
                type="text"
                placeholder="Solusi Teknologi Terintegrasi & Terukur"
                :disabled="formLoading"
              />
            </div>

            <div>
              <TextareaControl
                v-model="form.hero_subtitle"
                id="hero_subtitle"
                label="Sub-Judul Hero (Deskripsi Pengantar)"
                placeholder="Jelaskan secara singkat penawaran nilai produk atau perusahaan Anda..."
                :rows="3"
                :disabled="formLoading"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-border/40 pt-4">
              <InputControl
                v-model="form.hero_cta_text"
                id="hero_cta_text"
                label="Label Tombol CTA Utama"
                type="text"
                placeholder="Buka Portal Sistem"
                :disabled="formLoading"
              />

              <InputControl
                v-model="form.hero_cta_link"
                id="hero_cta_link"
                label="Tautan Tombol CTA Utama"
                type="text"
                placeholder="/login atau https://..."
                :disabled="formLoading"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- TAB 3: Profil Perusahaan -->
      <div v-show="activeTab === 'company'" class="space-y-6">
        <Card class="border-border/50 shadow-sm">
          <CardHeader class="pb-3">
            <CardTitle class="text-sm font-semibold">Tentang Perusahaan</CardTitle>
            <CardDescription class="text-xs">Informasi korporat, visi, misi, dan narasi perkenalan perusahaan.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <InputControl
                v-model="form.company_name"
                id="company_name"
                label="Nama Legal Perusahaan"
                type="text"
                placeholder="PT. Inovasi Digital Nusantara"
                :error="errors.company_name?.[0]"
                :disabled="formLoading"
              />

              <InputControl
                v-model="form.company_tagline"
                id="company_tagline"
                label="Tagline Perusahaan"
                type="text"
                placeholder="Empowering Businesses with Scalable Technology"
                :error="errors.company_tagline?.[0]"
                :disabled="formLoading"
              />
            </div>

            <div>
              <TextareaControl
                v-model="form.about_us"
                id="about_us"
                label="Tentang Kami (About Us)"
                placeholder="Sejarah, latar belakang, dan spesialisasi perusahaan..."
                :rows="4"
                :error="errors.about_us?.[0]"
                :disabled="formLoading"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <TextareaControl
                v-model="form.vision"
                id="vision"
                label="Visi Perusahaan"
                placeholder="Visi jangka panjang..."
                :rows="3"
                :error="errors.vision?.[0]"
                :disabled="formLoading"
              />

              <TextareaControl
                v-model="form.mission"
                id="mission"
                label="Misi Perusahaan"
                placeholder="Poin misi utama..."
                :rows="3"
                :error="errors.mission?.[0]"
                :disabled="formLoading"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- TAB 4: Kontak & Operasional -->
      <div v-show="activeTab === 'contact'" class="space-y-6">
        <Card class="border-border/50 shadow-sm">
          <CardHeader class="pb-3">
            <CardTitle class="text-sm font-semibold">Saluran Komunikasi & Jam Operasional</CardTitle>
            <CardDescription class="text-xs">Informasi kontak agar pengunjung dapat menghubungi Anda.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <InputControl
                v-model="form.contact_email"
                id="contact_email"
                label="Email Resmi"
                type="email"
                placeholder="info@perusahaan.com"
                :error="errors.contact_email?.[0]"
                :disabled="formLoading"
                @blur="() => validateField('contact_email')"
              >
                <template #prefix>
                  <Mail class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>

              <InputControl
                v-model="form.contact_phone"
                id="contact_phone"
                label="Telepon Kantor"
                type="text"
                placeholder="+62 21 555 1234"
                :error="errors.contact_phone?.[0]"
                :disabled="formLoading"
              >
                <template #prefix>
                  <Phone class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>

              <InputControl
                v-model="form.contact_whatsapp"
                id="contact_whatsapp"
                label="Nomor WhatsApp"
                type="text"
                placeholder="081234567890"
                :error="errors.contact_whatsapp?.[0]"
                :disabled="formLoading"
              >
                <template #prefix>
                  <MessageSquare class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <InputControl
                v-model="form.working_hours"
                id="working_hours"
                label="Jam Operasional"
                type="text"
                placeholder="Senin - Jumat: 08:30 - 17:30 WIB"
                :error="errors.working_hours?.[0]"
                :disabled="formLoading"
              >
                <template #prefix>
                  <Clock class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>

              <InputControl
                v-model="form.google_maps_embed"
                id="google_maps_embed"
                label="Google Maps URL / Embed Link"
                type="text"
                placeholder="https://maps.google.com/..."
                :error="errors.google_maps_embed?.[0]"
                :disabled="formLoading"
              >
                <template #prefix>
                  <MapPin class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>
            </div>

            <div>
              <TextareaControl
                v-model="form.contact_address"
                id="contact_address"
                label="Alamat Lengkap Kantor"
                placeholder="Gedung, Nama Jalan, Kota, Kode Pos..."
                :rows="2"
                :error="errors.contact_address?.[0]"
                :disabled="formLoading"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- TAB 5: Sosial Media & Legalitas -->
      <div v-show="activeTab === 'social_legal'" class="space-y-6">
        <Card class="border-border/50 shadow-sm">
          <CardHeader class="pb-3">
            <CardTitle class="text-sm font-semibold">Tautan Media Sosial & Legalitas</CardTitle>
            <CardDescription class="text-xs">Tautan profil resmi dan data legalitas perusahaan.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <InputControl
                v-model="form.social_instagram"
                id="social_instagram"
                label="Instagram URL"
                type="text"
                placeholder="https://instagram.com/akun"
                :error="errors.social_instagram?.[0]"
                :disabled="formLoading"
                @blur="() => validateField('social_instagram')"
              >
                <template #prefix>
                  <Instagram class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>

              <InputControl
                v-model="form.social_linkedin"
                id="social_linkedin"
                label="LinkedIn URL"
                type="text"
                placeholder="https://linkedin.com/company/akun"
                :error="errors.social_linkedin?.[0]"
                :disabled="formLoading"
                @blur="() => validateField('social_linkedin')"
              >
                <template #prefix>
                  <Linkedin class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>

              <InputControl
                v-model="form.social_facebook"
                id="social_facebook"
                label="Facebook URL"
                type="text"
                placeholder="https://facebook.com/akun"
                :error="errors.social_facebook?.[0]"
                :disabled="formLoading"
                @blur="() => validateField('social_facebook')"
              >
                <template #prefix>
                  <Facebook class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>

              <InputControl
                v-model="form.social_youtube"
                id="social_youtube"
                label="YouTube Channel URL"
                type="text"
                placeholder="https://youtube.com/@channel"
                :error="errors.social_youtube?.[0]"
                :disabled="formLoading"
                @blur="() => validateField('social_youtube')"
              >
                <template #prefix>
                  <Youtube class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>

              <InputControl
                v-model="form.social_twitter_x"
                id="social_twitter_x"
                label="X (Twitter) URL"
                type="text"
                placeholder="https://x.com/akun"
                :error="errors.social_twitter_x?.[0]"
                :disabled="formLoading"
                @blur="() => validateField('social_twitter_x')"
              >
                <template #prefix>
                  <Share2 class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>

              <InputControl
                v-model="form.social_github"
                id="social_github"
                label="GitHub Organization URL"
                type="text"
                placeholder="https://github.com/org"
                :error="errors.social_github?.[0]"
                :disabled="formLoading"
                @blur="() => validateField('social_github')"
              >
                <template #prefix>
                  <Github class="text-muted-foreground/50 h-3.5 w-3.5" />
                </template>
              </InputControl>
            </div>

            <div class="border-t border-border/40 pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <InputControl
                v-model="form.company_npwp"
                id="company_npwp"
                label="Nomor NPWP Perusahaan"
                type="text"
                placeholder="00.000.000.0-000.000"
                :disabled="formLoading"
              />

              <InputControl
                v-model="form.company_nib"
                id="company_nib"
                label="Nomor Induk Berusaha (NIB)"
                type="text"
                placeholder="1234567890123"
                :disabled="formLoading"
              />
            </div>

            <div>
              <InputControl
                v-model="form.footer_text"
                id="footer_text"
                label="Teks Hak Cipta Footer"
                type="text"
                placeholder="© 2026 PT Inovasi Digital. All rights reserved."
                :error="errors.footer_text?.[0]"
                :disabled="formLoading"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Action Bottom Bar -->
      <div class="flex items-center justify-between border-t border-border/40 pt-4">
        <span class="text-[11px] text-muted-foreground">Pastikan seluruh data terisi dengan valid sebelum menyimpan.</span>
        <Button type="submit" size="sm" :disabled="formLoading">
          <Loader2 v-if="formLoading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
          <Save v-else class="mr-1.5 h-3.5 w-3.5" />
          {{ formLoading ? 'Menyimpan...' : 'Simpan Semua Pengaturan' }}
        </Button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import * as z from 'zod'
import {
  Loader2,
  Upload,
  Save,
  Mail,
  Phone,
  MapPin,
  Clock,
  Building,
  Globe,
  Share2,
  AlertCircle,
  MessageSquare,
  Instagram,
  Linkedin,
  Facebook,
  Youtube,
  Github,
  Sparkles,
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import InputControl from '@/components/shared/input/input-control.vue'
import TextareaControl from '@/components/shared/input/textarea-control.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { useForm } from '@/composables/useForm'
import { useSettingStore } from '@/stores/setting-store'
import type { SettingFilesPayload } from '@/types'

const settingStore = useSettingStore()

const loading = ref(true)
const activeTab = ref('brand')

const tabList = [
  { id: 'brand', label: 'Brand & Media', icon: Globe },
  { id: 'hero', label: 'Hero & Landing', icon: Sparkles },
  { id: 'company', label: 'Profil Korporat', icon: Building },
  { id: 'contact', label: 'Kontak & Operasional', icon: Phone },
  { id: 'social_legal', label: 'Sosial Media & Legalitas', icon: Share2 },
]

// Schema validasi yang aman dari string kosong
const urlOrEmpty = z.union([z.string().url('Format URL tidak valid'), z.literal('')]).optional()

const schema = z.object({
  app_name: z.string().min(1, 'Nama aplikasi wajib diisi').max(255),
  app_slogan: z.string().max(255).optional().or(z.literal('')),
  hero_badge: z.string().max(255).optional().or(z.literal('')),
  hero_title: z.string().max(255).optional().or(z.literal('')),
  hero_subtitle: z.string().max(1000).optional().or(z.literal('')),
  hero_cta_text: z.string().max(100).optional().or(z.literal('')),
  hero_cta_link: z.string().max(255).optional().or(z.literal('')),
  company_name: z.string().max(255).optional().or(z.literal('')),
  company_tagline: z.string().max(255).optional().or(z.literal('')),
  app_description: z.string().max(1000).optional().or(z.literal('')),
  about_us: z.string().max(2000).optional().or(z.literal('')),
  vision: z.string().max(1000).optional().or(z.literal('')),
  mission: z.string().max(1000).optional().or(z.literal('')),
  contact_email: z.union([z.string().email('Format email tidak valid'), z.literal('')]).optional(),
  contact_phone: z.string().max(50).optional().or(z.literal('')),
  contact_whatsapp: z.string().max(50).optional().or(z.literal('')),
  contact_address: z.string().max(500).optional().or(z.literal('')),
  working_hours: z.string().max(255).optional().or(z.literal('')),
  google_maps_embed: z.string().optional().or(z.literal('')),
  social_facebook: urlOrEmpty,
  social_instagram: urlOrEmpty,
  social_twitter_x: urlOrEmpty,
  social_linkedin: urlOrEmpty,
  social_youtube: urlOrEmpty,
  social_tiktok: urlOrEmpty,
  social_github: urlOrEmpty,
  company_nib: z.string().max(100).optional().or(z.literal('')),
  company_npwp: z.string().max(100).optional().or(z.literal('')),
  footer_text: z.string().max(255).optional().or(z.literal('')),
})

type FormFields = {
  app_name: string
  app_slogan: string
  hero_badge: string
  hero_title: string
  hero_subtitle: string
  hero_cta_text: string
  hero_cta_link: string
  company_name: string
  company_tagline: string
  app_description: string
  about_us: string
  vision: string
  mission: string
  app_logo: string
  app_favicon: string
  hero_image: string
  contact_email: string
  contact_phone: string
  contact_whatsapp: string
  contact_address: string
  working_hours: string
  google_maps_embed: string
  social_facebook: string
  social_instagram: string
  social_twitter_x: string
  social_linkedin: string
  social_youtube: string
  social_tiktok: string
  social_github: string
  company_nib: string
  company_npwp: string
  footer_text: string
}

// Mapping tab untuk mendeteksi error
const tabFieldsMapping: Record<string, (keyof FormFields)[]> = {
  brand: ['app_name', 'app_slogan', 'app_description'],
  hero: ['hero_badge', 'hero_title', 'hero_subtitle', 'hero_cta_text', 'hero_cta_link'],
  company: ['company_name', 'company_tagline', 'about_us', 'vision', 'mission'],
  contact: ['contact_email', 'contact_phone', 'contact_whatsapp', 'working_hours', 'google_maps_embed', 'contact_address'],
  social_legal: ['social_instagram', 'social_linkedin', 'social_facebook', 'social_youtube', 'social_twitter_x', 'social_github', 'company_npwp', 'company_nib', 'footer_text'],
}

// State file & preview dinamis
const selectedFiles = reactive<SettingFilesPayload>({
  app_logo: null,
  app_favicon: null,
  hero_image: null,
})

const previewFiles = reactive<Record<string, string | null>>({
  app_logo: null,
  app_favicon: null,
  hero_image: null,
})

const initialFormData: FormFields = {
  app_name: '',
  app_slogan: '',
  hero_badge: '',
  hero_title: '',
  hero_subtitle: '',
  hero_cta_text: '',
  hero_cta_link: '',
  company_name: '',
  company_tagline: '',
  app_description: '',
  about_us: '',
  vision: '',
  mission: '',
  app_logo: '',
  app_favicon: '',
  hero_image: '',
  contact_email: '',
  contact_phone: '',
  contact_whatsapp: '',
  contact_address: '',
  working_hours: '',
  google_maps_embed: '',
  social_facebook: '',
  social_instagram: '',
  social_twitter_x: '',
  social_linkedin: '',
  social_youtube: '',
  social_tiktok: '',
  social_github: '',
  company_nib: '',
  company_npwp: '',
  footer_text: '',
}

const {
  form,
  errors,
  loading: formLoading,
  submit,
  validateField,
  reset,
} = useForm(initialFormData, { schema, autoFocusError: true })

const hasTabErrors = (tabId: string): boolean => {
  const fields = tabFieldsMapping[tabId] || []
  const errorObj = (errors as Record<string, any>).value ?? (errors as Record<string, any>)
  return fields.some((field) => {
    const err = errorObj[field]
    return Array.isArray(err) ? err.length > 0 : !!err
  })
}

const populateFormData = () => {
  const data = settingStore.settings as Record<string, any>
  const formRecord = form as unknown as Record<string, any>

  Object.keys(initialFormData).forEach((key) => {
    formRecord[key] = data[key] || ''
  })
}

onMounted(async () => {
  try {
    await settingStore.initializeSettings(true)
    reset()
    populateFormData()
  } catch (error) {
    console.error('Gagal memuat pengaturan:', error)
  } finally {
    loading.value = false
  }
})

const handleFileChange = (event: Event, fieldKey: 'app_logo' | 'app_favicon' | 'hero_image') => {
  const input = event.target as HTMLInputElement
  if (input.files && input.files[0]) {
    const file = input.files[0]
    selectedFiles[fieldKey] = file
    previewFiles[fieldKey] = URL.createObjectURL(file)
  }
}

const onSubmit = () => {
  submit(
    async (values: Record<string, any>) => {
      const payload: Record<string, any> = {}
      Object.keys(values).forEach((k) => {
        if (k !== 'app_logo' && k !== 'app_favicon' && k !== 'hero_image') {
          payload[k] = values[k] ?? ''
        }
      })

      const success = await settingStore.saveSettingsWithFiles(payload, selectedFiles)
      if (success) {
        await settingStore.initializeSettings(true)
        populateFormData()

        previewFiles.app_logo = null
        previewFiles.app_favicon = null
        previewFiles.hero_image = null
        selectedFiles.app_logo = null
        selectedFiles.app_favicon = null
        selectedFiles.hero_image = null
      }
    },
    {
      showSuccessToast: true,
      successMessage: 'Pengaturan sistem & profil perusahaan berhasil diperbarui',
      showErrorToast: true,
    }
  )

  for (const tab of tabList) {
    if (hasTabErrors(tab.id)) {
      activeTab.value = tab.id
      break
    }
  }
}
</script>