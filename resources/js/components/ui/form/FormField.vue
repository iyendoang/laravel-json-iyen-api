<script setup lang="ts">
import { computed } from "vue"

const props = defineProps<{
    name: string
    error?: string[]
}>()

const hasError = computed(() => !!props.error?.length)
</script>

<template>
    <div class="space-y-1.5">
        <slot :has-error="hasError" />

        <p
                v-if="hasError"
                class="text-destructive text-xs"
        >
            {{ error?.[0] }}
        </p>
    </div>
</template>