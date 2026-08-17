<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue'
import { useVModel } from '@vueuse/core'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableCell } from '@tiptap/extension-table-cell'
import { TableHeader } from '@tiptap/extension-table-header'
import TextAlign from '@tiptap/extension-text-align' // 🔥 PERBAIKAN: Menggunakan ekstensi resmi Tiptap TextAlign
import { cn } from '@/lib/utils'

import {
  Bold,
  Italic,
  Strikethrough,
  List,
  ListOrdered,
  Heading1,
  Heading2,
  Quote,
  Undo,
  Redo,
  CircleAlert,
  FileDown,
  Grid,
  AlignLeft,
  AlignCenter,
  AlignRight,
  AlignJustify
} from 'lucide-vue-next'

interface Props {
  modelValue?: string | null
  label?: string
  error?: string
  hint?: string
  placeholder?: string
  containerClass?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Tulis dokumen persuratan atau paste dari MS Word di sini...',
  containerClass: ''
})

const emit = defineEmits(['update:modelValue', 'blur'])

const modelValue = useVModel(props, 'modelValue', emit)

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Placeholder.configure({
      placeholder: () => props.placeholder
    }),
    Table.configure({
      resizable: true
    }),
    TableRow,
    TableHeader,
    TableCell,
    // 🔥 PERBAIKAN: Menggunakan TextAlign dengan konfigurasi target tag element
    TextAlign.configure({
      types: ['heading', 'paragraph'],
      alignments: ['left', 'center', 'right', 'justify']
    })
  ],
  onUpdate: ({ editor }) => {
    const html = editor.getHTML()
    modelValue.value = editor.isEmpty ? '' : html
  },
  onBlur: () => {
    emit('blur')
  }
})

watch(
  () => props.modelValue,
  (newValue) => {
    if (!editor.value) return
    const isSame = editor.value.getHTML() === newValue
    if (isSame) return
    editor.value.commands.setContent(newValue || '', { emitUpdate: false })
  }
)

onBeforeUnmount(() => {
  editor.value?.destroy()
})

const insertPageBreak = () => {
  if (!editor.value) return
  editor.value.chain().focus().insertContent('<div class="page-break"></div><p></p>').run()
}

const createTable = () => {
  if (!editor.value) return
  editor.value.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()
}
</script>

<template>
  <div :class="cn('flex w-full flex-col gap-1.5', props.containerClass)">
    <label
      v-if="label"
      class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
    >
      {{ label }}
    </label>

    <div
      :class="
        cn(
          'border-input bg-card flex min-h-[300px] w-full flex-col overflow-hidden rounded-xl border text-sm shadow-sm transition-all',
          error
            ? 'border-destructive focus-within:ring-destructive focus-within:ring-1'
            : 'focus-within:ring-ring focus-within:border-input focus-within:ring-1'
        )
      "
    >
      <div
        v-if="editor"
        class="bg-muted/40 flex shrink-0 flex-wrap items-center gap-1 border-b p-1.5"
      >
        <button
          type="button"
          @click="editor.chain().focus().toggleBold().run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive('bold') && 'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
        >
          <Bold class="h-3.5 w-3.5" />
        </button>

        <button
          type="button"
          @click="editor.chain().focus().toggleItalic().run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive('italic') && 'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
        >
          <Italic class="h-3.5 w-3.5" />
        </button>

        <button
          type="button"
          @click="editor.chain().focus().toggleStrike().run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive('strike') && 'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
        >
          <Strikethrough class="h-3.5 w-3.5" />
        </button>

        <div class="bg-border mx-1 h-4 w-[1px]"></div>

        <button
          type="button"
          @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive('heading', { level: 1 }) &&
                'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
        >
          <Heading1 class="h-3.5 w-3.5" />
        </button>

        <button
          type="button"
          @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive('heading', { level: 2 }) &&
                'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
        >
          <Heading2 class="h-3.5 w-3.5" />
        </button>

        <div class="bg-border mx-1 h-4 w-[1px]"></div>

        <button
          type="button"
          @click="editor.chain().focus().setTextAlign('left').run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive({ textAlign: 'left' }) &&
                'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
          title="Rata Kiri"
        >
          <AlignLeft class="h-3.5 w-3.5" />
        </button>

        <button
          type="button"
          @click="editor.chain().focus().setTextAlign('center').run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive({ textAlign: 'center' }) &&
                'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
          title="Rata Tengah"
        >
          <AlignCenter class="h-3.5 w-3.5" />
        </button>

        <button
          type="button"
          @click="editor.chain().focus().setTextAlign('right').run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive({ textAlign: 'right' }) &&
                'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
          title="Rata Kanan"
        >
          <AlignRight class="h-3.5 w-3.5" />
        </button>

        <button
          type="button"
          @click="editor.chain().focus().setTextAlign('justify').run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive({ textAlign: 'justify' }) &&
                'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
          title="Rata Kiri Kanan"
        >
          <AlignJustify class="h-3.5 w-3.5" />
        </button>

        <div class="bg-border mx-1 h-4 w-[1px]"></div>

        <button
          type="button"
          @click="editor.chain().focus().toggleBulletList().run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive('bulletList') && 'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
        >
          <List class="h-3.5 w-3.5" />
        </button>

        <button
          type="button"
          @click="editor.chain().focus().toggleOrderedList().run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive('orderedList') && 'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
        >
          <ListOrdered class="h-3.5 w-3.5" />
        </button>

        <button
          type="button"
          @click="editor.chain().focus().toggleBlockquote().run()"
          :class="
            cn(
              'text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors',
              editor.isActive('blockquote') && 'bg-primary/10 text-primary hover:bg-primary/15'
            )
          "
        >
          <Quote class="h-3.5 w-3.5" />
        </button>

        <div class="bg-border mx-1 h-4 w-[1px]"></div>

        <button
          type="button"
          @click="createTable"
          class="text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 transition-colors"
          title="Sisipkan Tabel"
        >
          <Grid class="h-3.5 w-3.5" />
        </button>

        <button
          type="button"
          @click="insertPageBreak"
          class="flex h-7 cursor-pointer items-center gap-1 rounded-md px-2 text-xs font-semibold text-emerald-600 transition-colors hover:bg-emerald-50"
          title="Sisipkan Batas Halaman Baru"
        >
          <FileDown class="h-3.5 w-3.5" />
          <span>+ Hal Baru</span>
        </button>

        <div class="ml-auto flex items-center gap-1">
          <button
            type="button"
            @click="editor.chain().focus().undo().run()"
            :disabled="!editor.can().undo()"
            class="text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 disabled:cursor-not-allowed disabled:opacity-40"
          >
            <Undo class="h-3.5 w-3.5" />
          </button>
          <button
            type="button"
            @click="editor.chain().focus().redo().run()"
            :disabled="!editor.can().redo()"
            class="text-muted-foreground hover:bg-muted hover:text-foreground flex h-7 w-7 cursor-pointer items-center justify-center rounded-md p-1 disabled:cursor-not-allowed disabled:opacity-40"
          >
            <Redo class="h-3.5 w-3.5" />
          </button>
        </div>
      </div>

      <div class="relative min-h-[220px] flex-1 overflow-y-auto p-4">
        <EditorContent :editor="editor" class="h-full focus:outline-none" />
        <div v-if="error" class="pointer-events-none absolute right-3 bottom-3">
          <CircleAlert class="text-destructive h-4 w-4" />
        </div>
      </div>
    </div>

    <p v-if="error" class="text-destructive text-xs italic transition-all">* {{ error }}</p>
    <p v-else-if="hint" class="text-muted-foreground text-[0.8rem]">{{ hint }}</p>
  </div>
</template>

<style>
.tiptap {
  min-height: 200px;
  height: 100%;
}

.tiptap:focus {
  outline: none;
}

.tiptap p.is-editor-empty:first-child::before {
  color: hsl(var(--muted-foreground));
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

.tiptap p {
  margin-bottom: 0.5rem;
  line-height: 1.6;
}

.tiptap h1 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

.tiptap h2 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 0.8rem;
  margin-bottom: 0.4rem;
}

.tiptap ul {
  list-style-type: disc;
  padding-left: 1.25rem;
  margin-bottom: 0.5rem;
}

.tiptap ol {
  list-style-type: decimal;
  padding-left: 1.25rem;
  margin-bottom: 0.5rem;
}

.tiptap blockquote {
  border-left: 3px solid hsl(var(--primary));
  padding-left: 0.75rem;
  color: hsl(var(--muted-foreground));
  font-style: italic;
  margin: 0.75rem 0;
}

.tiptap table {
  border-collapse: collapse !important;
  table-layout: fixed;
  width: 100% !important;
  margin: 0;
  overflow: hidden;
  margin-bottom: 1rem;
}

.tiptap table td,
.tiptap table th {
  min-width: 1em;
  border: 1px solid #cbd5e1 !important;
  padding: 8px 12px !important;
  vertical-align: top;
  box-sizing: border-box;
  position: relative;
}

.tiptap table th {
  background-color: hsl(var(--muted) / 0.5) !important;
  font-weight: bold;
  text-align: left;
}

.tiptap table .selectedCell:after {
  background: rgba(200, 200, 250, 0.4);
  content: '';
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  pointer-events: none;
  position: absolute;
  z-index: 2;
}

.tiptap .page-break {
  display: flex;
  align-items: center;
  justify-content: center;
  border-top: 1px dashed hsl(var(--muted-foreground) / 0.5);
  border-bottom: 1px dashed hsl(var(--muted-foreground) / 0.5);
  background-color: hsl(var(--muted) / 0.3);
  padding: 6px 0;
  margin: 20px 0;
  color: hsl(var(--muted-foreground));
  font-family: monospace;
  font-size: 10px;
  font-weight: bold;
  user-select: none;
}

.tiptap .page-break::before {
  content: '--- BATAS POTONGAN HALAMAN (PAGE BREAK) ---';
  letter-spacing: 0.15em;
}
</style>
