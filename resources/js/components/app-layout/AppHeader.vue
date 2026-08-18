<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  ChevronRight,
  LogOut,
  User,
  Settings,
} from 'lucide-vue-next'
import ThemeToggle from '@/components/ThemeToggle.vue'
import ThemePicker from '@/components/ThemeColorPicker.vue'
import { useAuthStore } from '@/stores/auth-store'
import { SidebarTrigger } from '@/components/ui/sidebar'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const handleLogout = async () => {
  await auth.logout()
  await router.push('/login')
}

// Breadcrumb dengan path dan status aktif
const breadcrumbs = computed(() => {
  return route.matched
    .filter((r) => r.meta?.title)
    .map((r) => ({
      title: r.meta.title as string,
      path: r.path,
    }))
})

// Inisial user untuk fallback avatar
const userInitial = computed(() =>
  auth.user?.name ? auth.user.name.charAt(0).toUpperCase() : '?'
)

// Formatting role langsung dari string auth.user.role
const formattedRole = computed(() => {
  const roleName = auth.user?.role || 'User'
  return roleName.replace(/[-_]/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase())
})
</script>

<template>
  <header
    class="border-border/60 bg-background/80 sticky top-0 z-30 flex h-14 w-full items-center justify-between border-b px-4 shadow-xs backdrop-blur-md transition-all duration-200 select-none"
  >
    <!-- Left: Sidebar Trigger & Dynamic Breadcrumbs -->
    <div class="flex items-center gap-3">
      <SidebarTrigger
        class="text-muted-foreground hover:bg-muted hover:text-foreground h-8 w-8 rounded-lg transition-colors"
      />

      <div class="bg-border/60 hidden h-4 w-px md:block" />

      <!-- Breadcrumbs -->
      <nav class="hidden items-center gap-1.5 text-xs font-medium md:flex" aria-label="Breadcrumb">
        <template v-for="(crumb, index) in breadcrumbs" :key="crumb.path">
          <ChevronRight v-if="index > 0" class="text-muted-foreground/40 h-3.5 w-3.5 shrink-0" />

          <router-link
            v-if="index < breadcrumbs.length - 1"
            :to="crumb.path"
            class="text-muted-foreground hover:text-foreground transition-colors"
          >
            {{ crumb.title }}
          </router-link>
          <span
            v-else
            class="text-foreground font-semibold"
            aria-current="page"
          >
            {{ crumb.title }}
          </span>
        </template>
      </nav>
    </div>

    <!-- Right: Theme Picker & User Profile Dropdown -->
    <div class="flex items-center gap-2.5">
      <!-- Theme Controls -->
      <div class="border-border/60 bg-card/60 flex h-8 items-center rounded-lg border px-1.5 py-0.5 shadow-2xs">
        <ThemePicker />
        <div class="bg-border/60 mx-1.5 h-3.5 w-px" />
        <ThemeToggle />
      </div>

      <!-- User Dropdown Menu -->
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <button
            class="hover:ring-primary/40 relative flex h-8 w-8 items-center justify-center rounded-full p-0 transition-all outline-none focus-visible:ring-2 focus-visible:ring-primary"
            aria-label="User menu"
          >
            <!-- Avatar Foto jika ada -->
            <div
              v-if="auth.user?.avatar"
              class="h-8 w-8 overflow-hidden rounded-full border border-border/80"
            >
              <img
                :src="auth.user.avatar"
                :alt="auth.user.name"
                class="h-full w-full object-cover"
              />
            </div>

            <!-- Avatar Fallback Inisial -->
            <div
              v-else
              class="bg-primary/10 text-primary border border-primary/20 flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold shadow-2xs"
            >
              <span>{{ userInitial }}</span>
            </div>
          </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent
          align="end"
          side="bottom"
          :side-offset="8"
          class="border-border/60 bg-popover/95 w-60 rounded-xl border p-1 shadow-lg backdrop-blur-md"
        >
          <!-- User Summary Header -->
          <DropdownMenuLabel class="px-3 py-2.5 font-normal">
            <div class="flex flex-col space-y-1.5 leading-none">
              <div class="flex items-center justify-between">
                <span class="text-foreground truncate text-xs font-bold">
                  {{ auth.user?.name || 'Administrator' }}
                </span>
                <span
                  class="bg-primary/10 text-primary border border-primary/20 inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-semibold tracking-wide capitalize"
                >
                  {{ formattedRole }}
                </span>
              </div>
              <span class="text-muted-foreground truncate text-[11px] font-medium">
                {{ auth.user?.email || 'admin@domain.com' }}
              </span>
            </div>
          </DropdownMenuLabel>

          <DropdownMenuSeparator class="bg-border/60" />

          <!-- Navigasi Menu Profil & Pengaturan -->
          <DropdownMenuItem
            as-child
            class="hover:bg-muted/80 cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors"
          >
            <router-link to="/admin/profile" class="flex items-center w-full">
              <User class="mr-2 h-3.5 w-3.5 text-muted-foreground" />
              Profil Saya
            </router-link>
          </DropdownMenuItem>

          <DropdownMenuItem
            as-child
            class="hover:bg-muted/80 cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors"
          >
            <router-link to="/admin/settings" class="flex items-center w-full">
              <Settings class="mr-2 h-3.5 w-3.5 text-muted-foreground" />
              Pengaturan Sistem
            </router-link>
          </DropdownMenuItem>

          <DropdownMenuSeparator class="bg-border/60" />

          <!-- Logout Button -->
          <DropdownMenuItem
            @click="handleLogout"
            class="text-destructive focus:text-destructive focus:bg-destructive/10 cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors"
          >
            <LogOut class="mr-2 h-3.5 w-3.5" />
            Keluar Aplikasi
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  </header>
</template>