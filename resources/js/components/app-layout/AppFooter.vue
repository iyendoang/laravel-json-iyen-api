<script setup lang="ts">
/**
 * Footer – Compact / Clean / Professional
 */
</script>

<template>
    <footer
            class="relative mt-auto border-t border-border/30 bg-background/70 backdrop-blur-md
           px-4 py-2 transition-colors duration-300"
    >
        <div class="flex items-center justify-between text-[11px]">

            <!-- LEFT -->
            <div class="flex items-center gap-2 text-muted-foreground/60">
                <span>&copy; 2026</span>

                <span class="text-foreground/80 font-semibold tracking-tight">
          Core System
        </span>

                <span class="opacity-30">•</span>

                <span class="hidden sm:inline text-[10px] tracking-wider uppercase">
          Enterprise
        </span>
            </div>

            <!-- RIGHT -->
            <div class="flex items-center gap-4 text-muted-foreground/50">

                <a
                        href="#"
                        class="transition-colors hover:text-foreground"
                >
                    Privacy
                </a>

                <a
                        href="#"
                        class="transition-colors hover:text-foreground"
                >
                    Docs
                </a>

                <a
                        href="#"
                        class="transition-colors hover:text-foreground"
                >
                    Support
                </a>

            </div>

        </div>
    </footer>
</template>

<style scoped>
footer {
    transition:
            border-color 0.3s ease,
            background-color 0.3s ease;
}
</style>