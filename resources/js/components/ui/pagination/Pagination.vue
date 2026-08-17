<script setup lang="ts">
import type {PaginationRootEmits, PaginationRootProps} from "reka-ui"
import type {HTMLAttributes} from "vue"
import {reactiveOmit} from "@vueuse/core"
import {PaginationRoot, useForwardPropsEmits} from "reka-ui"
import {cn} from "@/lib/utils"

interface Props extends PaginationRootProps {
  class?: HTMLAttributes["class"]
}

const props = defineProps<Props>()
const emits = defineEmits<PaginationRootEmits>()

const delegatedProps = reactiveOmit(props, "class")
const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <PaginationRoot
    v-slot="slotProps"
    data-slot="pagination"
    v-bind="forwarded"
    :class="cn('inline-flex items-center gap-1', props.class)"
  >
    <slot v-bind="slotProps"/>
  </PaginationRoot>
</template>