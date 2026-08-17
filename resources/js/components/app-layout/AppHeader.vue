<script setup lang="ts">
import {computed} from 'vue'
import {useRoute} from 'vue-router'
import {ChevronRight, LogOut} from 'lucide-vue-next'
import ThemeToggle from '@/components/ThemeToggle.vue'
import ThemePicker from '@/components/ThemeColorPicker.vue'
import {useAuthStore} from '@/stores/auth-store'
import {SidebarTrigger} from '@/components/ui/sidebar'
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

const handleLogout = async () => {
  await auth.logout()
}

const breadcrumbs = computed(() =>
  route.matched
    .filter((r) => r.meta?.title)
    .map((r) => ({
      title: r.meta.title as string,
      path: r.path,
    }))
)

const userInitial = computed(() =>
  auth.user?.name ? auth.user.name.charAt(0).toUpperCase() : '?'
)

const formattedRole = computed(() => {
  const role = auth.user?.role || ''
  return role.replace(/-/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase())
})
</script>

<template>
  <header
    class="border-border/40 bg-background/80 sticky top-0 z-30 flex h-12 w-full items-center justify-between border-b px-4 shadow-sm backdrop-blur-md transition-all duration-200 select-none">
    <div class="flex items-center gap-3">
      <SidebarTrigger
        class="text-muted-foreground/80 hover:bg-accent hover:text-foreground h-8 w-8 rounded-md transition-colors"/>

      <div class="bg-border/40 hidden h-4 w-px md:block"/>

      <nav class="hidden items-center gap-1.5 text-xs font-medium md:flex">
        <template v-for="(crumb, index) in breadcrumbs" :key="crumb.path">
          <ChevronRight v-if="index > 0" class="text-muted-foreground/40 size-3 shrink-0"/>
          <span :class="[
                        index === breadcrumbs.length - 1
                            ? 'text-foreground font-semibold'
                            : 'text-muted-foreground/70'
                    ]">
                        {{ crumb.title }}
                    </span>
        </template>
      </nav>
    </div>

    <div class="flex items-center gap-3">
      <div class="border-border/30 bg-muted/40 flex h-7.5 items-center rounded-lg border px-1.5 py-0.5">
        <ThemePicker/>
        <div class="bg-border/30 mx-1.5 h-3.5 w-px"/>
        <ThemeToggle/>
      </div>

      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <button
            class="hover:bg-accent/50 relative flex size-7.5 items-center justify-center rounded-full p-0 focus-visible:ring-0">
            <div
              class="bg-primary/10 text-primary ring-primary/20 flex size-7 items-center justify-center rounded-full text-xs font-bold ring-1">
              <span>{{ userInitial }}</span>
            </div>
          </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" side="bottom" :side-offset="6"
                             class="border-border/40 bg-background w-56 rounded-lg border p-1 shadow-md">
          <DropdownMenuLabel class="px-2.5 py-2 font-normal">
            <div class="flex flex-col space-y-1 leading-none">
                            <span class="text-foreground truncate text-xs font-semibold">
                                {{ auth.user?.name }}
                            </span>
              <span class="text-muted-foreground truncate text-[10px] font-medium">
                                {{ auth.user?.email }}
                            </span>
              <span
                class="bg-primary/10 text-primary inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold tracking-wide capitalize">
                                {{ formattedRole }}
                            </span>
            </div>
          </DropdownMenuLabel>

          <DropdownMenuSeparator class="bg-border/40"/>

          <DropdownMenuItem @click="handleLogout"
                            class="text-destructive focus:text-destructive focus:bg-destructive/10 cursor-pointer rounded-md px-2.5 py-1.5 text-xs font-medium">
            <LogOut class="mr-2 size-3.5"/>
            Keluar Aplikasi
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  </header>
</template>