<script setup lang="ts">
import AppSidebar from '@/components/app-layout/AppSidebar.vue'
import AppHeader from '@/components/app-layout/AppHeader.vue'
import AppFooter from '@/components/app-layout/AppFooter.vue'
import {SidebarInset, SidebarProvider} from '@/components/ui/sidebar'
</script>

<template>
  <SidebarProvider>
    <AppSidebar/>

    <SidebarInset class="bg-background flex flex-col">
      <AppHeader
        class="transition-[height] duration-200 ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12"
      />

      <main class="flex-1 overflow-y-auto">
        <div class="px-6 py-4">
          <slot/>
        </div>
      </main>

      <AppFooter/>
    </SidebarInset>
  </SidebarProvider>
</template>