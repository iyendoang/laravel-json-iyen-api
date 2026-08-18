<template>
  <div class="w-full min-h-screen text-foreground selection:bg-primary/20 selection:text-primary">
    <!-- 0. Sticky Navbar Header -->
    <header class="sticky top-3 sm:top-4 z-50 w-full max-w-5xl mx-auto px-3 sm:px-6">
      <nav
        class="flex items-center justify-between px-3.5 sm:px-5 py-2.5 sm:py-3 rounded-2xl border border-border/70 bg-background/80 backdrop-blur-xl shadow-sm transition-all">
        <!-- Logo & Nama Brand -->
        <router-link to="/" class="flex items-center gap-2.5 sm:gap-3 group">
          <div
            class="h-8 w-8 sm:h-9 sm:w-9 rounded-xl border border-primary/25 bg-primary/10 p-1 flex items-center justify-center overflow-hidden shrink-0 transition-transform group-hover:scale-105">
            <img
              v-if="settingStore.appLogo"
              :src="settingStore.appLogo"
              :alt="settingStore.appName"
              class="h-full w-full object-contain"
            />
            <Building2 v-else class="h-4 w-4 sm:h-5 sm:w-5 text-primary"/>
          </div>

          <!-- Hidden di Mobile, Tampil mulai dari Tablet/Desktop (sm:block) -->
          <div class="text-left hidden sm:block">
            <span class="font-bold text-xs sm:text-sm tracking-tight text-foreground line-clamp-1">
              {{ settingStore.appName }}
            </span>
            <span class="text-[10px] text-muted-foreground block -mt-0.5 line-clamp-1 max-w-[140px] sm:max-w-xs">
              {{ settingStore.companyInfo.tagline || 'Enterprise Platform' }}
            </span>
          </div>
        </router-link>

        <!-- Right Side: Theme Controls & Auth Navigation -->
        <div class="flex items-center gap-1.5 sm:gap-2">
          <!-- Theme Picker & Toggle (Desktop & Tablet) -->
          <div class="hidden sm:flex items-center gap-1 border border-border/50 bg-muted/30 rounded-xl p-0.5">
            <ThemeColorPicker/>
            <div class="bg-border/60 h-3.5 w-[1px] mx-0.5"/>
            <ThemeToggle/>
          </div>

          <!-- Mobile Theme Toggle -->
          <div class="flex sm:hidden items-center gap-1">
            <ThemeToggleMobile/>
          </div>

          <router-link
            to="/login"
            class="inline-flex items-center justify-center px-2.5 sm:px-3.5 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors"
          >
            Masuk
          </router-link>

          <router-link
            to="/register"
            class="inline-flex items-center justify-center px-3 sm:px-4 py-1.5 rounded-lg text-xs font-semibold bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all active:scale-[0.98]"
          >
            <span>Daftar</span>
            <ArrowRight class="ml-1 h-3.5 w-3.5 hidden sm:inline"/>
          </router-link>
        </div>
      </nav>
    </header>

    <!-- Main Body Container -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 space-y-16 sm:space-y-24 pt-6 sm:pt-10 pb-16">
      <!-- 1. Hero Section -->
      <section class="relative text-center pt-4 sm:pt-10 space-y-8">
        <!-- Ambient Decorative Glow -->
        <div
          class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 sm:w-[500px] h-64 sm:h-[350px] bg-primary/15 blur-[100px] sm:blur-[140px] rounded-full pointer-events-none -z-10"/>

        <!-- Tagline / Badge Pill -->
        <div
          class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-primary/10 border border-primary/20 text-primary text-[11px] sm:text-xs font-semibold shadow-xs">
          <Sparkles class="h-3.5 w-3.5 shrink-0 animate-pulse"/>
          <span class="line-clamp-1">{{ settingStore.settings.hero_badge || settingStore.companyInfo.tagline || settingStore.appSlogan || 'Transformasi Digital Terpadu' }}</span>
        </div>

        <!-- Main Title & Description -->
        <div class="space-y-3 sm:space-y-4 max-w-3xl mx-auto">
          <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.15] text-foreground">
            {{ settingStore.settings.hero_title || settingStore.companyInfo.name || settingStore.appName }}
          </h1>
          <p class="text-muted-foreground text-xs sm:text-base sm:leading-relaxed max-w-2xl mx-auto px-2">
            {{ settingStore.settings.hero_subtitle || settingStore.companyInfo.description || settingStore.settings.app_description || 'Platform backend dan sistem manajemen data terintegrasi yang andal, aman, dan siap menskalakan operasional bisnis Anda.' }}
          </p>
        </div>

        <!-- Hero Image Banner Showcase (Jika diupload di Admin) -->
        <div v-if="settingStore.settings.hero_image" class="max-w-4xl mx-auto pt-2">
          <div class="rounded-2xl border border-border/70 bg-card/60 backdrop-blur-md p-2 shadow-lg overflow-hidden">
            <img
              :src="settingStore.settings.hero_image"
              alt="Hero Banner"
              class="w-full h-auto max-h-[420px] object-cover rounded-xl"
            />
          </div>
        </div>

        <!-- Action CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2 max-w-xs sm:max-w-none mx-auto">
          <component
            :is="settingStore.settings.hero_cta_link?.startsWith('http') ? 'a' : 'router-link'"
            :to="!settingStore.settings.hero_cta_link?.startsWith('http') ? (settingStore.settings.hero_cta_link || '/login') : undefined"
            :href="settingStore.settings.hero_cta_link?.startsWith('http') ? settingStore.settings.hero_cta_link : undefined"
            :target="settingStore.settings.hero_cta_link?.startsWith('http') ? '_blank' : undefined"
            class="w-full sm:w-auto bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center justify-center rounded-xl px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-semibold shadow-md shadow-primary/20 transition-all active:scale-[0.98]"
          >
            <LogIn class="mr-2 h-4 w-4"/>
            {{ settingStore.settings.hero_cta_text || 'Buka Portal Sistem' }}
          </component>

          <a
            v-if="settingStore.appContact.whatsapp"
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full sm:w-auto border border-border/80 bg-background/80 hover:bg-muted/80 text-foreground inline-flex items-center justify-center rounded-xl px-5 py-2.5 sm:py-3 text-xs sm:text-sm font-medium shadow-xs transition-all active:scale-[0.98]"
          >
            <MessageCircle class="mr-2 h-4 w-4 text-emerald-500"/>
            Konsultasi WhatsApp
          </a>
        </div>

        <!-- Legal Badge Ribbon -->
        <div v-if="settingStore.companyInfo.nib || settingStore.companyInfo.npwp"
             class="pt-2 flex flex-wrap items-center justify-center gap-2 sm:gap-4 text-[10px] sm:text-xs text-muted-foreground">
          <span v-if="settingStore.companyInfo.nib"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-muted/40 border border-border/40">
            <CheckCircle2 class="h-3.5 w-3.5 text-primary shrink-0"/> NIB: {{ settingStore.companyInfo.nib }}
          </span>
          <span v-if="settingStore.companyInfo.npwp"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-muted/40 border border-border/40">
            <CheckCircle2 class="h-3.5 w-3.5 text-primary shrink-0"/> NPWP: {{ settingStore.companyInfo.npwp }}
          </span>
        </div>
      </section>

      <!-- 2. Bento-Grid Corporate Profile & Values -->
      <section v-if="settingStore.companyInfo.aboutUs || settingStore.companyInfo.vision" class="space-y-6">
        <div class="text-center space-y-1">
          <h2 class="text-xl sm:text-3xl font-bold tracking-tight text-foreground">Profil & Fondasi Bisnis</h2>
          <p class="text-xs sm:text-sm text-muted-foreground">Nilai inti dan dedikasi yang menggerakkan perkembangan kami</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-5">
          <!-- About Us Card -->
          <div
            class="md:col-span-12 rounded-2xl border border-border/60 bg-card/70 backdrop-blur-md p-5 sm:p-8 shadow-xs relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 opacity-5 text-primary pointer-events-none hidden sm:block">
              <Building class="h-48 w-48"/>
            </div>
            <div class="space-y-3 relative z-10">
              <div
                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-xs font-semibold">
                <Building class="h-3.5 w-3.5"/>
                Tentang Kami
              </div>
              <h3 class="text-base sm:text-xl font-bold text-foreground">Membangun Ekosistem Digital Berkelanjutan</h3>
              <p class="text-muted-foreground text-xs sm:text-sm leading-relaxed whitespace-pre-line text-left">
                {{ settingStore.companyInfo.aboutUs }}
              </p>
            </div>
          </div>

          <!-- Vision Card -->
          <div v-if="settingStore.companyInfo.vision"
               class="md:col-span-5 rounded-2xl border border-border/60 bg-card/70 backdrop-blur-md p-5 sm:p-7 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5 sm:space-y-3">
              <div
                class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                <Target class="h-4 w-4 sm:h-5 sm:w-5"/>
              </div>
              <h4 class="text-sm sm:text-base font-bold text-foreground">Visi Perusahaan</h4>
              <p class="text-muted-foreground text-xs sm:text-sm leading-relaxed text-left">
                {{ settingStore.companyInfo.vision }}
              </p>
            </div>
          </div>

          <!-- Mission Card -->
          <div v-if="settingStore.companyInfo.mission"
               :class="[settingStore.companyInfo.vision ? 'md:col-span-7' : 'md:col-span-12']"
               class="rounded-2xl border border-border/60 bg-card/70 backdrop-blur-md p-5 sm:p-7 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5 sm:space-y-3">
              <div
                class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                <Compass class="h-4 w-4 sm:h-5 sm:w-5"/>
              </div>
              <h4 class="text-sm sm:text-base font-bold text-foreground">Misi Utama</h4>
              <p class="text-muted-foreground text-xs sm:text-sm leading-relaxed whitespace-pre-line text-left">
                {{ settingStore.companyInfo.mission }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- 3. Key Advantages / Features Matrix -->
      <section class="space-y-6 sm:space-y-8">
        <div class="text-center space-y-1">
          <h2 class="text-xl sm:text-3xl font-bold tracking-tight text-foreground">Keunggulan Arsitektur Sistem</h2>
          <p class="text-xs sm:text-sm text-muted-foreground">Infrastruktur modern dengan performa dan keandalan tinggi</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
          <div
            class="group rounded-2xl border border-border/60 bg-card p-5 sm:p-6 shadow-xs space-y-2.5 transition-all hover:shadow-md hover:border-primary/50">
            <div
              class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center transition-transform group-hover:scale-105">
              <ShieldCheck class="h-5 w-5 sm:h-6 sm:w-6"/>
            </div>
            <h3 class="text-sm sm:text-base font-semibold text-foreground">Security & Token Control</h3>
            <p class="text-xs leading-relaxed text-muted-foreground">Enkripsi REST API terpadu dengan sistem JWT Blacklist real-time untuk keamanan menyeluruh.</p>
          </div>

          <div
            class="group rounded-2xl border border-border/60 bg-card p-5 sm:p-6 shadow-xs space-y-2.5 transition-all hover:shadow-md hover:border-primary/50">
            <div
              class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center transition-transform group-hover:scale-105">
              <Users class="h-5 w-5 sm:h-6 sm:w-6"/>
            </div>
            <h3 class="text-sm sm:text-base font-semibold text-foreground">Granular Access Level</h3>
            <p class="text-xs leading-relaxed text-muted-foreground">Manajemen otorisasi multi-role dan permission spesifik untuk proteksi data sensitif.</p>
          </div>

          <div
            class="group rounded-2xl border border-border/60 bg-card p-5 sm:p-6 shadow-xs space-y-2.5 transition-all hover:shadow-md hover:border-primary/50">
            <div
              class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center transition-transform group-hover:scale-105">
              <Zap class="h-5 w-5 sm:h-6 sm:w-6"/>
            </div>
            <h3 class="text-sm sm:text-base font-semibold text-foreground">High Scalability</h3>
            <p class="text-xs leading-relaxed text-muted-foreground">Ditenagai stack Vue 3, Pinia, dan Tailwind CSS untuk pengalaman rendering antarmuka yang cepat.</p>
          </div>
        </div>
      </section>

      <!-- 4. Interactive Call to Action Banner -->
      <section
        class="rounded-3xl border border-border/60 bg-gradient-to-br from-primary/15 via-background to-background p-6 sm:p-12 text-center space-y-5 sm:space-y-6 shadow-sm">
        <div class="max-w-2xl mx-auto space-y-2">
          <h2 class="text-xl sm:text-3xl font-extrabold text-foreground tracking-tight">Siap Memulai Transformasi?</h2>
          <p class="text-muted-foreground text-xs sm:text-sm leading-relaxed">
            Daftarkan akun administrator Anda dan nikmati kemudahan konfigurasi sistem terpusat sekarang juga.
          </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 max-w-xs sm:max-w-none mx-auto">
          <router-link
            to="/register"
            class="w-full sm:w-auto bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center justify-center rounded-xl px-6 py-2.5 text-xs sm:text-sm font-semibold shadow-xs transition-all active:scale-[0.98]"
          >
            Registrasi Akun Baru
          </router-link>
          <router-link
            to="/login"
            class="w-full sm:w-auto border border-border bg-background hover:bg-muted text-foreground inline-flex items-center justify-center rounded-xl px-6 py-2.5 text-xs sm:text-sm font-medium transition-all active:scale-[0.98]"
          >
            Akses Akun Saya
          </router-link>
        </div>
      </section>

      <!-- 5. Structured Responsive Footer -->
      <footer class="border-t border-border/60 pt-10 sm:pt-12 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Col 1: Brand Summary -->
          <div class="space-y-3 text-left">
            <div class="flex items-center gap-2.5">
              <div
                class="h-8 w-8 rounded-lg border border-primary/20 bg-primary/10 p-1 flex items-center justify-center">
                <img
                  v-if="settingStore.appLogo"
                  :src="settingStore.appLogo"
                  :alt="settingStore.appName"
                  class="h-full w-full object-contain"
                />
                <Building2 v-else class="h-4 w-4 text-primary"/>
              </div>
              <span class="font-bold text-sm text-foreground">{{ settingStore.appName }}</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ settingStore.companyInfo.tagline || 'Solusi infrastruktur data dan sistem manajemen korporat terdepan.' }}
            </p>
          </div>

          <!-- Col 2: Operational Contacts -->
          <div class="space-y-3 text-left">
            <h4 class="text-xs font-bold uppercase tracking-wider text-foreground">Kontak & Lokasi</h4>
            <div class="space-y-2.5 text-xs text-muted-foreground">
              <div v-if="settingStore.appContact.address" class="flex items-start gap-2.5">
                <MapPin class="h-4 w-4 text-primary shrink-0 mt-0.5"/>
                <span>{{ settingStore.appContact.address }}</span>
              </div>
              <div v-if="settingStore.appContact.email" class="flex items-center gap-2.5">
                <Mail class="h-4 w-4 text-primary shrink-0"/>
                <a :href="`mailto:${settingStore.appContact.email}`" class="hover:underline hover:text-foreground">
                  {{ settingStore.appContact.email }}
                </a>
              </div>
              <div v-if="settingStore.appContact.phone" class="flex items-center gap-2.5">
                <Phone class="h-4 w-4 text-primary shrink-0"/>
                <span>{{ settingStore.appContact.phone }}</span>
              </div>
              <div v-if="settingStore.appContact.workingHours" class="flex items-center gap-2.5">
                <Clock class="h-4 w-4 text-primary shrink-0"/>
                <span>{{ settingStore.appContact.workingHours }}</span>
              </div>
            </div>
          </div>

          <!-- Col 3: Social Media Links -->
          <div class="space-y-3 text-left md:text-right">
            <h4 class="text-xs font-bold uppercase tracking-wider text-foreground">Koneksi Sosial</h4>
            <p class="text-xs text-muted-foreground">Kunjungi kanal resmi kami:</p>
            <div class="flex flex-wrap gap-2 pt-1 md:justify-end">
              <a
                v-if="settingStore.socialLinks.linkedin"
                :href="settingStore.socialLinks.linkedin"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 rounded-lg border border-border/60 bg-card hover:bg-muted text-foreground transition-colors"
                title="LinkedIn"
              >
                <Linkedin class="h-4 w-4"/>
              </a>

              <a
                v-if="settingStore.socialLinks.instagram"
                :href="settingStore.socialLinks.instagram"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 rounded-lg border border-border/60 bg-card hover:bg-muted text-foreground transition-colors"
                title="Instagram"
              >
                <Instagram class="h-4 w-4"/>
              </a>

              <a
                v-if="settingStore.socialLinks.facebook"
                :href="settingStore.socialLinks.facebook"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 rounded-lg border border-border/60 bg-card hover:bg-muted text-foreground transition-colors"
                title="Facebook"
              >
                <Facebook class="h-4 w-4"/>
              </a>

              <a
                v-if="settingStore.socialLinks.youtube"
                :href="settingStore.socialLinks.youtube"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 rounded-lg border border-border/60 bg-card hover:bg-muted text-foreground transition-colors"
                title="YouTube"
              >
                <Youtube class="h-4 w-4"/>
              </a>

              <a
                v-if="settingStore.socialLinks.github"
                :href="settingStore.socialLinks.github"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 rounded-lg border border-border/60 bg-card hover:bg-muted text-foreground transition-colors"
                title="GitHub"
              >
                <Github class="h-4 w-4"/>
              </a>
            </div>
          </div>
        </div>

        <!-- Copyright Bottom Notice -->
        <div class="border-t border-border/40 pt-6 text-center text-xs text-muted-foreground">
          <p>{{ settingStore.footerText }}</p>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed} from 'vue'
import {useSettingStore} from '@/stores/setting-store'
import ThemeToggle from '@/components/ThemeToggle.vue'
import ThemeColorPicker from '@/components/ThemeColorPicker.vue'
import ThemeToggleMobile from '@/components/ThemeToggleMobile.vue'
import {
  LogIn,
  ShieldCheck,
  Users,
  Sparkles,
  Building,
  Building2,
  Target,
  Compass,
  MapPin,
  Mail,
  Phone,
  Clock,
  MessageCircle,
  Linkedin,
  Instagram,
  Facebook,
  Youtube,
  Github,
  ArrowRight,
  CheckCircle2,
  Zap,
} from 'lucide-vue-next'

const settingStore = useSettingStore()

const whatsappUrl = computed(() => {
  const phone = settingStore.appContact.whatsapp || ''
  const cleanPhone = phone.replace(/[^0-9]/g, '')
  const formattedPhone = cleanPhone.startsWith('0') ? '62' + cleanPhone.substring(1) : cleanPhone
  return `https://wa.me/${formattedPhone}?text=Halo%20${encodeURIComponent(settingStore.companyInfo.name || 'Admin')},%20saya%20tertarik%20dengan%20layanan%20Anda.`
})
</script>