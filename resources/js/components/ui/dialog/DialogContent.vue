<script setup lang="ts">
import type { DialogContentEmits, DialogContentProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { X } from 'lucide-vue-next'
import {
    DialogClose,
    DialogContent,
    DialogPortal,
    useForwardPropsEmits,
} from 'reka-ui'
import { cn } from '@/lib/utils'
import DialogOverlay from './DialogOverlay.vue'

// 1. Tambahkan konfigurasi prop 'size' opsional dengan default ke ukuran standar ('md')
interface ExtendedProps extends DialogContentProps {
    class?: HTMLAttributes['class']
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full'
}

const props = withDefaults(defineProps<ExtendedProps>(), {
    size: 'md'
})

const emits = defineEmits<DialogContentEmits>()

// 2. Omit properti kelas dan size agar tidak ikut diteruskan mentah-mentah ke bnding reka-ui
const delegatedProps = reactiveOmit(props, 'class', 'size')
const forwarded = useForwardPropsEmits(delegatedProps, emits)

// 3. Mapping kelas ukuran Tailwind secara terpusat untuk menjaga kesucian kerapian kode
const sizeClasses = {
    sm: 'sm:max-w-sm rounded-lg',
    md: 'sm:max-w-lg rounded-lg',
    lg: 'sm:max-w-2xl rounded-lg',
    xl: 'sm:max-w-5xl rounded-lg',
    full: 'max-w-full w-screen h-screen top-0 left-0 translate-x-0 translate-y-0 rounded-none border-none'
}
</script>

<template>
    <DialogPortal>
        <DialogOverlay />
        <DialogContent
                data-slot="dialog-content"
                v-bind="forwarded"
                :class="
        cn(
          // Kelas dasar modal layout animasi
          'bg-background data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 fixed z-50 grid gap-4 border p-6 shadow-lg duration-200',
          // Kelas posisi tengah dinamis (hanya aktif jika ukurannya BUKAN full screen)
          props.size !== 'full' ? 'top-[50%] left-[50%] translate-x-[-50%] translate-y-[-50%] w-full max-w-[calc(100%-2rem)]' : '',
          // Suntikkan kelas ukuran reaktif berdasarkan props
          sizeClasses[props.size],
          props.class,
        )"
        >
            <slot />

            <DialogClose
                    class="ring-offset-background focus:ring-ring data-[state=open]:bg-accent data-[state=open]:text-muted-foreground absolute top-4 right-4 rounded-xs opacity-70 transition-opacity hover:opacity-100 focus:ring-2 focus:ring-offset-2 focus:outline-hidden disabled:pointer-events-none [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 cursor-pointer"
            >
                <X />
                <span class="sr-only">Close</span>
            </DialogClose>
        </DialogContent>
    </DialogPortal>
</template>