<script setup lang="ts">
import {computed, watch, onUnmounted} from 'vue'
import {Loader2, Lock, Check, Circle} from 'lucide-vue-next'
import {useSystemProcessOverlay} from '@/composables/useSystemProcessOverlay'

const {state} = useSystemProcessOverlay()

// Mencegah penutupan atau refresh tab saat proses kritis
const preventUnload = (e: BeforeUnloadEvent) => {
  e.preventDefault()
  e.returnValue = ''
}

watch(
  () => state.show,
  (isActive) => {
    if (isActive) {
      window.addEventListener('beforeunload', preventUnload)
      document.body.style.overflow = 'hidden'
    } else {
      window.removeEventListener('beforeunload', preventUnload)
      document.body.style.overflow = ''
    }
  },
  {immediate: true}
)

onUnmounted(() => {
  window.removeEventListener('beforeunload', preventUnload)
  document.body.style.overflow = ''
})

const computedProgress = computed(() => {
  if (typeof state.percentage === 'number' && state.percentage > 0) {
    return Math.min(100, Math.max(0, state.percentage))
  }
  if (state.totalSteps > 0 && typeof state.currentStep === 'number') {
    return Math.min(100, Math.max(0, Math.round((state.currentStep / state.totalSteps) * 100)))
  }
  return null
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="state.show"
        class="fixed inset-0 z-100 flex items-center justify-center bg-background/50 p-4 backdrop-blur-md select-none"
      >
        <div
          class="bg-background/95 border-border/70 shadow-2xl relative w-full max-w-sm overflow-hidden rounded-2xl border p-5 backdrop-blur-xl"
        >
          <!-- Ambient Glow -->
          <div
            class="bg-primary/10 pointer-events-none absolute -top-10 -left-10 h-32 w-32 rounded-full blur-3xl"
          />

          <div class="relative space-y-3.5">
            <!-- Header Status -->
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-2.5 min-w-0">
                <div
                  class="bg-primary/10 text-primary ring-1 ring-primary/25 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
                >
                  <Loader2 class="size-3.5 animate-spin"/>
                </div>
                <div class="truncate">
                  <h4 class="text-foreground text-xs font-semibold tracking-tight truncate">
                    {{ state.title }}
                  </h4>
                  <p class="text-muted-foreground text-[10px] truncate leading-tight">
                    {{ state.description }}
                  </p>
                </div>
              </div>

              <!-- Badge Step Counter / Lock -->
              <span
                v-if="state.totalSteps > 0"
                class="bg-primary/10 text-primary ring-1 ring-primary/20 inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 font-mono text-[9px] font-semibold"
              >
                {{ state.currentStep }}/{{ state.totalSteps }}
              </span>
              <span
                v-else
                class="bg-muted/70 text-muted-foreground/80 ring-1 ring-border/40 inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-medium"
              >
                <Lock class="size-2.5"/>
                I/O Locked
              </span>
            </div>

            <!-- Command Live Terminal Output -->
            <div
              class="bg-muted/30 border-border/40 flex items-center justify-between rounded-lg border px-2.5 py-1.5 font-mono text-[10px]"
            >
              <div class="flex items-center gap-1.5 truncate">
                <span class="text-primary font-bold">›</span>
                <span class="text-foreground/80 truncate">{{ state.statusStep }}</span>
              </div>
              <span
                v-if="computedProgress !== null"
                class="text-primary font-semibold shrink-0 pl-2 text-[10px]"
              >
                {{ computedProgress }}%
              </span>
              <span v-else class="text-muted-foreground/60 shrink-0 pl-2 text-[9px]">live</span>
            </div>

            <!-- Progress Track -->
            <div class="bg-muted/60 relative h-1 w-full overflow-hidden rounded-full">
              <div
                v-if="computedProgress !== null"
                class="bg-primary h-full rounded-full transition-all duration-300 ease-out"
                :style="{ width: `${computedProgress}%` }"
              />
              <div
                v-else
                class="bg-primary absolute inset-y-0 h-full w-1/3 rounded-full animate-[progress_1.6s_easeInOut_infinite]"
              />
            </div>

            <!-- Task Items List (Opsional jika bertahap) -->
            <div
              v-if="state.items && state.items.length > 0"
              class="bg-muted/20 border-border/40 max-h-28 overflow-y-auto rounded-lg border p-1.5 space-y-1 divide-y divide-border/20"
            >
              <div
                v-for="(item, idx) in state.items"
                :key="item.id || idx"
                class="flex items-center justify-between px-1.5 py-1 text-[10px]"
              >
                <span
                  class="truncate pr-2 font-mono"
                  :class="[
                    item.status === 'completed' ? 'text-muted-foreground line-through' :
                    item.status === 'processing' ? 'text-foreground font-semibold' :
                    'text-muted-foreground/70'
                  ]"
                >
                  {{ item.label }}
                </span>

                <span class="shrink-0">
                  <Check v-if="item.status === 'completed'" class="size-3 text-emerald-500"/>
                  <Loader2 v-else-if="item.status === 'processing'" class="size-3 text-primary animate-spin"/>
                  <Circle v-else class="size-2 text-muted-foreground/40"/>
                </span>
              </div>
            </div>

            <!-- Minimalist Hint -->
            <p class="text-muted-foreground/60 text-center text-[10px]">
              Jangan menutup jendela peramban hingga proses selesai.
            </p>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
@keyframes progress {
  0% {
    left: -35%;
    width: 30%;
  }
  50% {
    left: 40%;
    width: 45%;
  }
  100% {
    left: 105%;
    width: 25%;
  }
}
</style>