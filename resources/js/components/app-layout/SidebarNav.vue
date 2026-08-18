<template>
  <SidebarGroup class="py-1.5 px-0 select-none">
    <!-- Group Header Label -->
    <SidebarGroupLabel
      v-if="label"
      class="text-muted-foreground/60 px-3 py-1.5 text-[10px] font-bold tracking-wider uppercase group-data-[state=collapsed]:hidden"
    >
      {{ label }}
    </SidebarGroupLabel>

    <SidebarMenu class="gap-1 px-1.5">
      <template v-for="(item, index) in items" :key="getItemKey(item, index)">
        <!-- 1. Menu Divider -->
        <div v-if="isMenuDivider(item)" class="border-border/50 my-2 border-t px-2">
          <span
            v-if="item.title"
            class="text-muted-foreground/60 block pt-1.5 text-[9px] font-bold tracking-wider uppercase group-data-[state=collapsed]:hidden"
          >
            {{ item.title }}
          </span>
        </div>

        <!-- 2. Menu Leaf (Single Action / Route) -->
        <SidebarMenuItem v-else-if="isMenuLeaf(item)">
          <SidebarMenuButton
            as-child
            :is-active="isItemActive(item)"
            :tooltip="item.tooltip || item.title"
            :disabled="item.disabled"
            class="group h-9 w-full rounded-xl px-2.5 text-xs font-medium transition-all duration-150"
            :class="[
              item.disabled
                ? 'opacity-50 pointer-events-none cursor-not-allowed'
                : 'hover:bg-muted/80 hover:text-foreground text-muted-foreground',
              isItemActive(item)
                ? 'bg-primary/10 text-primary font-semibold shadow-2xs'
                : ''
            ]"
          >
            <!-- External URL Link -->
            <a
              v-if="item.external"
              :href="item.routeName"
              :target="item.target || '_blank'"
              rel="noopener noreferrer"
              class="flex w-full items-center gap-2.5"
            >
              <component
                :is="item.icon"
                v-if="item.icon"
                class="h-4 w-4 shrink-0 transition-colors"
                :class="isItemActive(item) ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'"
              />
              <span class="truncate flex-1 group-data-[state=collapsed]:hidden">
                {{ item.title }}
              </span>

              <!-- Badge -->
              <span
                v-if="item.badge"
                class="rounded-md px-1.5 py-0.5 text-[9px] font-bold tracking-tight group-data-[state=collapsed]:hidden"
                :class="item.badgeColor || 'bg-primary/15 text-primary border border-primary/20'"
              >
                {{ item.badge }}
              </span>
            </a>

            <!-- Internal RouterLink -->
            <RouterLink
              v-else
              :to="{ name: item.routeName }"
              class="flex w-full items-center gap-2.5"
            >
              <component
                :is="item.icon"
                v-if="item.icon"
                class="h-4 w-4 shrink-0 transition-colors"
                :class="isItemActive(item) ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'"
              />
              <span class="truncate flex-1 group-data-[state=collapsed]:hidden">
                {{ item.title }}
              </span>

              <!-- Badge -->
              <span
                v-if="item.badge"
                class="rounded-md px-1.5 py-0.5 text-[9px] font-bold tracking-tight group-data-[state=collapsed]:hidden"
                :class="item.badgeColor || 'bg-primary/15 text-primary border border-primary/20'"
              >
                {{ item.badge }}
              </span>
            </RouterLink>
          </SidebarMenuButton>
        </SidebarMenuItem>

        <!-- 3. Menu Parent (Collapsible Sub-menu Group) -->
        <Collapsible
          v-else-if="isMenuParent(item)"
          :open="isGroupExpanded(item)"
          class="group/collapsible"
        >
          <SidebarMenuItem>
            <CollapsibleTrigger as-child>
              <SidebarMenuButton
                :tooltip="item.tooltip || item.title"
                :is-active="isItemOrChildActive(item)"
                class="group flex h-9 w-full items-center gap-2.5 rounded-xl px-2.5 text-xs font-medium transition-all duration-150 hover:bg-muted/80 hover:text-foreground"
                :class="[
                  isItemOrChildActive(item)
                    ? 'text-primary font-semibold'
                    : 'text-muted-foreground'
                ]"
                @click="toggleGroup(item.title)"
              >
                <component
                  :is="item.icon"
                  v-if="item.icon"
                  class="h-4 w-4 shrink-0 transition-colors"
                  :class="isItemOrChildActive(item) ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'"
                />
                <span class="truncate flex-1 text-left group-data-[state=collapsed]:hidden">
                  {{ item.title }}
                </span>

                <!-- Badge Parent -->
                <span
                  v-if="item.badge"
                  class="mr-1.5 rounded-md px-1.5 py-0.5 text-[9px] font-bold tracking-tight group-data-[state=collapsed]:hidden"
                  :class="item.badgeColor || 'bg-primary/15 text-primary border border-primary/20'"
                >
                  {{ item.badge }}
                </span>

                <!-- Arrow Indicator -->
                <ChevronRight
                  class="text-muted-foreground/60 ml-auto h-3.5 w-3.5 shrink-0 transition-transform duration-200 ease-in-out group-data-[state=collapsed]:hidden group-data-[state=open]/collapsible:rotate-90"
                />
              </SidebarMenuButton>
            </CollapsibleTrigger>

            <!-- Collapsible Children Sub-menu -->
            <CollapsibleContent
              class="data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down overflow-hidden"
            >
              <SidebarMenuSub
                class="border-border/60 mx-3.5 mt-1 space-y-0.5 border-l pl-2.5 group-data-[state=collapsed]:hidden"
              >
                <SidebarMenuSubItem
                  v-for="child in item.children"
                  :key="child.routeName || child.title"
                >
                  <SidebarMenuSubButton
                    as-child
                    :is-active="isItemActive(child)"
                    :disabled="child.disabled"
                    class="group h-8 w-full rounded-lg px-2 text-xs font-medium transition-all duration-150"
                    :class="[
                      child.disabled
                        ? 'opacity-50 pointer-events-none cursor-not-allowed'
                        : 'hover:bg-muted/80 hover:text-foreground text-muted-foreground',
                      isItemActive(child)
                        ? 'bg-primary/10 text-primary font-semibold'
                        : ''
                    ]"
                  >
                    <!-- External Sub-link -->
                    <a
                      v-if="child.external"
                      :href="child.routeName"
                      :target="child.target || '_blank'"
                      rel="noopener noreferrer"
                      class="flex w-full items-center gap-2"
                    >
                      <component
                        :is="child.icon"
                        v-if="child.icon"
                        class="h-3.5 w-3.5 shrink-0 transition-colors"
                        :class="isItemActive(child) ? 'text-primary' : 'text-muted-foreground/70 group-hover:text-foreground'"
                      />
                      <span class="truncate flex-1">{{ child.title }}</span>

                      <span
                        v-if="child.badge"
                        class="rounded px-1.5 py-0.2 text-[9px] font-bold"
                        :class="child.badgeColor || 'bg-primary/15 text-primary border border-primary/20'"
                      >
                        {{ child.badge }}
                      </span>
                    </a>

                    <!-- Internal Sub-link -->
                    <RouterLink
                      v-else
                      :to="{ name: child.routeName }"
                      class="flex w-full items-center gap-2"
                    >
                      <component
                        :is="child.icon"
                        v-if="child.icon"
                        class="h-3.5 w-3.5 shrink-0 transition-colors"
                        :class="isItemActive(child) ? 'text-primary' : 'text-muted-foreground/70 group-hover:text-foreground'"
                      />
                      <span class="truncate flex-1">{{ child.title }}</span>

                      <span
                        v-if="child.badge"
                        class="rounded px-1.5 py-0.2 text-[9px] font-bold"
                        :class="child.badgeColor || 'bg-primary/15 text-primary border border-primary/20'"
                      >
                        {{ child.badge }}
                      </span>
                    </RouterLink>
                  </SidebarMenuSubButton>
                </SidebarMenuSubItem>
              </SidebarMenuSub>
            </CollapsibleContent>
          </SidebarMenuItem>
        </Collapsible>
      </template>
    </SidebarMenu>
  </SidebarGroup>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { ChevronRight } from 'lucide-vue-next'
import { useMenu } from '@/composables/useMenu'
import type { MenuItem, MenuLeaf, MenuParent } from '@/types/menu'

import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible'

import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
} from '@/components/ui/sidebar'

defineProps<{
  label?: string
  items: MenuItem[]
}>()

const {
  isItemActive,
  isItemOrChildActive,
  isMenuLeaf,
  isMenuParent,
  isMenuDivider,
  isGroupExpanded,
  toggleGroup,
} = useMenu()

const getItemKey = (item: MenuItem, index: number): string => {
  if ('routeName' in item && (item as MenuLeaf).routeName) {
    return (item as MenuLeaf).routeName
  }
  if ('title' in item && item.title) {
    return `${item.title}-${index}`
  }
  return `divider-${index}`
}
</script>