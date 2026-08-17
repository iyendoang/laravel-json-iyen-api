<script setup lang="ts">
import {computed} from 'vue'
import {RouterLink} from 'vue-router'
import {useMenu} from '@/composables/useMenu'
import {useAuthStore} from '@/stores/auth-store'
import {useSettingStore} from '@/stores/setting-store'
import {LogOut, User, ChevronUp, ShieldCheck} from 'lucide-vue-next'

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
} from '@/components/ui/dropdown-menu'

import SidebarNav from './SidebarNav.vue'

const {filteredGroups} = useMenu()
const auth = useAuthStore()
const settingStore = useSettingStore()

const formattedRole = computed(() => {
  const role = auth.user?.role || ''
  return role.replace(/-/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase())
})
</script>

<template>
  <Sidebar collapsible="icon" class="border-border/40 bg-background border-r">
    <!-- Header -->
    <SidebarHeader
      class="border-border/40 flex h-12 items-center border-b px-3 transition-all group-data-[state=collapsed]:px-2">
      <RouterLink
        :to="{ name: 'dashboard' }"
        class="flex h-full w-full items-center gap-2.5 transition-all group-data-[state=collapsed]:justify-center"
      >
        <div
          class="bg-primary/10 border-primary/20 flex size-6.5 shrink-0 items-center justify-center overflow-hidden rounded-md border shadow-xs transition-all group-data-[state=collapsed]:size-7">
          <img
            v-if="settingStore.appLogo"
            :src="settingStore.appLogo"
            alt="App Logo"
            class="h-full w-full object-contain p-1"
          />
          <ShieldCheck v-else class="text-primary size-4"/>
        </div>

        <div class="flex flex-col overflow-hidden leading-tight group-data-[state=collapsed]:hidden">
                    <span class="text-foreground w-32 truncate text-[11px] font-bold tracking-tight uppercase">
                        {{ settingStore.appName || 'App Name' }}
                    </span>
          <span class="text-muted-foreground mt-0.5 w-36 truncate text-[10px] leading-tight font-medium capitalize">
                        {{ settingStore.appSlogan || 'Aplikasi Utama' }}
                    </span>
        </div>
      </RouterLink>
    </SidebarHeader>

    <!-- Content -->
    <SidebarContent class="gap-0 py-2">
      <SidebarNav
        v-for="group in filteredGroups"
        :key="group.heading"
        :label="group.heading"
        :items="group.items"
      />
    </SidebarContent>

    <!-- Footer -->
    <SidebarFooter class="border-border/40 border-t p-2 group-data-[state=collapsed]:p-1.5">
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <button
            class="hover:bg-accent/50 flex w-full items-center gap-2 rounded-lg p-1.5 text-xs transition-all group-data-[state=collapsed]:justify-center group-data-[state=collapsed]:p-1 focus-visible:outline-none">
            <div
              class="bg-primary/10 text-primary ring-primary/20 flex h-6.5 w-6.5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold ring-1">
              {{ auth.user?.name?.charAt(0).toUpperCase() || '?' }}
            </div>

            <div
              class="flex flex-col items-start overflow-hidden text-left leading-tight group-data-[state=collapsed]:hidden">
                            <span class="text-foreground w-28 truncate text-xs font-semibold">
                                {{ auth.user?.name || 'User' }}
                            </span>
              <span class="text-muted-foreground mt-0.5 text-[10px] capitalize">
                                {{ formattedRole }}
                            </span>
            </div>

            <ChevronUp class="ml-auto size-3.5 group-data-[state=collapsed]:hidden"/>
          </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent side="top" align="start"
                             class="border-border/40 bg-background w-52 rounded-lg border p-1 shadow-md">
          <DropdownMenuItem as-child>
            <RouterLink :to="{ name: 'profile' }" class="cursor-pointer rounded-md px-2.5 py-2 text-xs font-medium">
              <User class="mr-2 h-3.5 w-3.5"/>
              Profil Saya
            </RouterLink>
          </DropdownMenuItem>

          <div class="my-1 border-t border-border/30"/>

          <DropdownMenuItem @click="auth.logout()"
                            class="text-destructive focus:text-destructive focus:bg-destructive/10 cursor-pointer rounded-md px-2.5 py-2 text-xs font-medium">
            <LogOut class="mr-2 h-3.5 w-3.5"/>
            Keluar Aplikasi
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarFooter>

    <SidebarRail/>
  </Sidebar>
</template>