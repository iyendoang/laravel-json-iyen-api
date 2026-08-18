<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useMenu } from '@/composables/useMenu'
import { useAuthStore } from '@/stores/auth-store'
import { useSettingStore } from '@/stores/setting-store'
import { LogOut, User, ChevronsUpDown, ShieldCheck, Settings } from 'lucide-vue-next'

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarRail,
} from '@/components/ui/sidebar'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'

import SidebarNav from './SidebarNav.vue'

const router = useRouter()
const { filteredGroups } = useMenu()
const auth = useAuthStore()
const settingStore = useSettingStore()

const userInitial = computed(() =>
  auth.user?.name ? auth.user.name.charAt(0).toUpperCase() : '?'
)

const formattedRole = computed(() => {
  const role = auth.user?.role || 'User'
  return role.replace(/[-_]/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase())
})

const handleLogout = async () => {
  await auth.logout()
  await router.push('/login')
}
</script>

<template>
  <Sidebar collapsible="icon" class="border-border/60 bg-sidebar border-r select-none">
    <!-- Header: Brand & App Title -->
    <SidebarHeader class="border-border/60 flex h-14 items-center border-b px-3 transition-all group-data-[state=collapsed]:px-2">
      <RouterLink
        :to="{ name: 'dashboard' }"
        class="flex h-full w-full items-center gap-2.5 transition-all group-data-[state=collapsed]:justify-center group"
      >
        <div
          class="bg-primary/10 border-primary/20 flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-xl border shadow-2xs transition-transform group-hover:scale-105"
        >
          <img
            v-if="settingStore.appLogo"
            :src="settingStore.appLogo"
            :alt="settingStore.appName"
            class="h-full w-full object-contain p-1"
          />
          <ShieldCheck v-else class="text-primary h-4.5 w-4.5" />
        </div>

        <div class="flex flex-col min-w-0 flex-1 leading-tight group-data-[state=collapsed]:hidden text-left">
          <span class="text-foreground truncate text-xs font-bold tracking-tight">
            {{ settingStore.appName || 'Laravel App' }}
          </span>
          <span class="text-muted-foreground truncate text-[10px] font-medium opacity-80">
            {{ settingStore.companyInfo?.tagline || settingStore.appSlogan || 'Enterprise System' }}
          </span>
        </div>
      </RouterLink>
    </SidebarHeader>

    <!-- Navigation Menu Items -->
    <SidebarContent class="gap-1 py-3 px-2">
      <SidebarNav
        v-for="group in filteredGroups"
        :key="group.heading"
        :label="group.heading"
        :items="group.items"
      />
    </SidebarContent>

    <!-- Footer: User Profile & Actions -->
    <SidebarFooter class="border-border/60 border-t p-2">
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <button
            class="hover:bg-muted/80 flex w-full items-center gap-2.5 rounded-xl p-1.5 text-xs transition-colors group-data-[state=collapsed]:justify-center group-data-[state=collapsed]:p-1 outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
            aria-label="User Account"
          >
            <!-- Avatar with Image or Initial Fallback -->
            <div
              v-if="auth.user?.avatar"
              class="h-8 w-8 shrink-0 overflow-hidden rounded-full border border-border/80"
            >
              <img
                :src="auth.user.avatar"
                :alt="auth.user.name"
                class="h-full w-full object-cover"
              />
            </div>
            <div
              v-else
              class="bg-primary/10 text-primary border-primary/20 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border text-[11px] font-bold shadow-2xs"
            >
              <span>{{ userInitial }}</span>
            </div>

            <!-- Profile Info in Expanded State -->
            <div class="flex flex-col items-start min-w-0 flex-1 text-left leading-tight group-data-[state=collapsed]:hidden">
              <span class="text-foreground w-full truncate text-xs font-semibold">
                {{ auth.user?.name || 'Administrator' }}
              </span>
              <span class="text-muted-foreground w-full truncate text-[10px] font-medium capitalize">
                {{ formattedRole }}
              </span>
            </div>

            <ChevronsUpDown class="text-muted-foreground/60 ml-auto h-3.5 w-3.5 shrink-0 group-data-[state=collapsed]:hidden" />
          </button>
        </DropdownMenuTrigger>

        <!-- Dropdown Menu Content -->
        <DropdownMenuContent
          side="top"
          align="start"
          :side-offset="8"
          class="border-border/60 bg-popover/95 w-56 rounded-xl border p-1 shadow-lg backdrop-blur-md"
        >
          <DropdownMenuLabel class="px-2.5 py-2 font-normal">
            <div class="flex flex-col space-y-1 leading-none">
              <div class="flex items-center justify-between">
                <span class="text-foreground truncate text-xs font-semibold">
                  {{ auth.user?.name || 'Administrator' }}
                </span>
                <span class="bg-primary/10 text-primary border-primary/20 inline-flex items-center rounded border px-1.5 py-0.2 text-[9px] font-bold capitalize">
                  {{ formattedRole }}
                </span>
              </div>
              <span class="text-muted-foreground truncate text-[10px] font-medium">
                {{ auth.user?.email || 'user@example.com' }}
              </span>
            </div>
          </DropdownMenuLabel>

          <DropdownMenuSeparator class="bg-border/60" />

          <DropdownMenuItem as-child class="hover:bg-muted/80 cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors">
            <RouterLink :to="{ name: 'profile' }" class="flex items-center w-full">
              <User class="mr-2 h-3.5 w-3.5 text-muted-foreground" />
              Profil Saya
            </RouterLink>
          </DropdownMenuItem>

          <DropdownMenuItem as-child class="hover:bg-muted/80 cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors">
            <RouterLink to="/admin/settings" class="flex items-center w-full">
              <Settings class="mr-2 h-3.5 w-3.5 text-muted-foreground" />
              Pengaturan Sistem
            </RouterLink>
          </DropdownMenuItem>

          <DropdownMenuSeparator class="bg-border/60" />

          <DropdownMenuItem
            @click="handleLogout"
            class="text-destructive focus:text-destructive focus:bg-destructive/10 cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors"
          >
            <LogOut class="mr-2 h-3.5 w-3.5" />
            Keluar Aplikasi
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarFooter>

    <SidebarRail />
  </Sidebar>
</template>