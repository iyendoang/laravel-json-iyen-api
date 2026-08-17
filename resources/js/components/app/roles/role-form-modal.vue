<script setup lang="ts">
import { computed, watch, ref, onMounted } from 'vue'
import { z } from 'zod'
import { Save, X, CircleAlert, Search } from 'lucide-vue-next'
import { useForm } from '@/composables/useForm'
import { roleService, type Role } from '@/services/v1/role-service'
import { permissionService, type Permission } from '@/services/v1/permission-service'
import { useAuthStore } from '@/store/auth-store'

import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from '@/components/ui/dialog'
import InputControl from '@/components/shared/input/input-control.vue'
import SelectControl from '@/components/shared/input/select-control.vue'
import AppButton from '@/components/shared/app-button.vue'
import AppBadge from '@/components/shared/app-badge.vue'

const auth = useAuthStore()

const GUARD_OPTIONS = [
    { value: 'web', label: 'Web' },
    { value: 'api', label: 'API' }
]

// Permission list untuk dipilih
const allPermissions = ref<Permission[]>([])
const permissionSearch = ref('')
const selectedPermissions = ref<string[]>([])

// Load permissions
onMounted(async () => {
    try {
        const perms = await permissionService.getAllDropdown()
        allPermissions.value = perms
    } catch (error) {
        console.error('Failed to load permissions:', error)
    }
})

// Filter permissions by search
const filteredPermissions = computed(() => {
    if (!permissionSearch.value) return allPermissions.value
    return allPermissions.value.filter((p) =>
        p.name.toLowerCase().includes(permissionSearch.value.toLowerCase())
    )
})

// Group permissions by prefix
const groupedPermissions = computed(() => {
    const groups: Record<string, Permission[]> = {}

    filteredPermissions.value.forEach((p) => {
        const prefix = p.name.split(' ')[0] || 'other'
        if (!groups[prefix]) groups[prefix] = []
        groups[prefix].push(p)
    })

    return Object.entries(groups).sort(([a], [b]) => a.localeCompare(b))
})

/* =========================================================
   FORM
========================================================= */
const roleSchema = z.object({
    name: z
        .string()
        .trim()
        .min(1, 'Nama role wajib diisi')
        .max(255, 'Nama terlalu panjang')
        .regex(
            /^[a-z-]+$/,
            'Gunakan huruf kecil dan dash (contoh: super-admin)'
        ),
    guard_name: z.enum(['web', 'api'])
})

type RoleForm = z.infer<typeof roleSchema>

interface Props {
    open: boolean
    role?: Role | null
}

const props = defineProps<Props>()
const emit = defineEmits(['update:open', 'saved'])

const { form, errors, loading, submit, reset } = useForm<RoleForm>(
    {
        name: '',
        guard_name: 'web'
    },
    {
        schema: roleSchema,
        autoFocusError: true
    }
)

const getError = (field: string) => {
    const err = (errors.value as any)[field]
    return Array.isArray(err) ? err[0] : err
}

const isEdit = computed(() => !!props.role)
const isSuperAdmin = computed(() => props.role?.name === 'super-admin')
const canEditSuperAdmin = computed(() => auth.isSuperAdmin)

// Toggle permission selection
const togglePermission = (permissionName: string) => {
    const index = selectedPermissions.value.indexOf(permissionName)
    if (index > -1) {
        selectedPermissions.value.splice(index, 1)
    } else {
        selectedPermissions.value.push(permissionName)
    }
}

const selectAllInGroup = (group: [string, Permission[]]) => {
    const names = group[1].map((p) => p.name)
    const allSelected = names.every((n) => selectedPermissions.value.includes(n))

    if (allSelected) {
        selectedPermissions.value = selectedPermissions.value.filter((n) => !names.includes(n))
    } else {
        names.forEach((n) => {
            if (!selectedPermissions.value.includes(n)) {
                selectedPermissions.value.push(n)
            }
        })
    }
}

const isGroupFullySelected = (group: [string, Permission[]]): boolean => {
    return group[1].every((p) => selectedPermissions.value.includes(p.name))
}

const isGroupPartiallySelected = (group: [string, Permission[]]): boolean => {
    return group[1].some((p) => selectedPermissions.value.includes(p.name))
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            reset()
            if (props.role) {
                form.name = props.role.name
                form.guard_name = props.role.guard_name as 'web' | 'api'
                selectedPermissions.value = props.role.permissions?.map((p) => p.name) || []
            } else {
                form.name = ''
                form.guard_name = 'web'
                selectedPermissions.value = []
            }
            permissionSearch.value = ''
        }
    }
)

const handleSubmit = async () => {
    await submit(
        async (values) => {
            const payload = {
                name: values.name,
                guard_name: values.guard_name,
                permissions: selectedPermissions.value
            }

            if (isEdit.value) {
                return roleService.update(props.role!.id, payload)
            }
            return roleService.create(payload)
        },
        {
            onSuccess: () => {
                emit('saved')
                emit('update:open', false)
            }
        }
    )
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent size="xl" >
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <span class="bg-primary h-2.5 w-2.5 rounded-full"></span>
                    <span class="font-bold tracking-tight">
            {{ isEdit ? 'Edit Role' : 'Tambah Role Baru' }}
          </span>
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-1" autocomplete="off">
                <!-- Error General -->
                <div
                        v-if="errors._general"
                        class="bg-destructive/10 text-destructive flex items-center gap-2 rounded-lg p-3 text-xs font-medium"
                >
                    <CircleAlert class="h-4 w-4 shrink-0" />
                    {{ getError('_general') }}
                </div>

                <!-- Nama Role -->
                <InputControl
                        v-model="form.name"
                        label="Nama Role"
                        name="name"
                        placeholder="Contoh: editor, moderator"
                        hint="Huruf kecil dan dash. Contoh: super-admin"
                        :error="getError('name')"
                        :disabled="isSuperAdmin && !canEditSuperAdmin"
                />

                <!-- Guard -->
                <SelectControl
                        v-slot
                        v-model="form.guard_name"
                        name="guard_name"
                        label="Guard"
                        :options="GUARD_OPTIONS"
                        :error="getError('guard_name')"
                        placeholder="Pilih guard"
                />

                <!-- Permissions Section -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">Permissions</label>

                    <!-- Search Permissions -->
                    <div class="relative">
                        <Search class="text-muted-foreground absolute left-2.5 top-2.5 h-3.5 w-3.5" />
                        <input
                                v-model="permissionSearch"
                                type="text"
                                placeholder="Cari permission..."
                                class="border-border bg-background h-8 w-full rounded-md border pl-8 pr-3 text-xs"
                        />
                    </div>

                    <!-- Selected Count -->
                    <div class="flex items-center gap-2">
                        <AppBadge variant="default" size="sm">
                            {{ selectedPermissions.length }} dipilih
                        </AppBadge>
                        <button
                                v-if="selectedPermissions.length > 0"
                                type="button"
                                class="text-muted-foreground text-xs hover:underline"
                                @click="selectedPermissions = []"
                        >
                            Clear all
                        </button>
                    </div>

                    <!-- Permission Groups -->
                    <div class="border-border max-h-60 space-y-3 overflow-y-auto rounded-md border p-3">
                        <div v-for="group in groupedPermissions" :key="group[0]" class="space-y-1">
                            <!-- Group Header -->
                            <button
                                    type="button"
                                    class="bg-muted/50 flex w-full items-center gap-2 rounded px-2 py-1 text-xs font-semibold capitalize"
                                    @click="selectAllInGroup(group)"
                            >
                                <div
                                        class="border-primary flex h-3.5 w-3.5 items-center justify-center rounded border"
                                        :class="{
                    'bg-primary border-primary': isGroupFullySelected(group),
                    'border-2': isGroupPartiallySelected(group)
                  }"
                                >
                                    <Check v-if="isGroupFullySelected(group)" class="h-3 w-3 text-white" />
                                    <Minus v-else-if="isGroupPartiallySelected(group)" class="h-3 w-3 text-primary" />
                                </div>
                                {{ group[0] }}
                            </button>

                            <!-- Group Items -->
                            <div class="ml-4 space-y-0.5">
                                <label
                                        v-for="perm in group[1]"
                                        :key="perm.id"
                                        class="hover:bg-accent/50 flex cursor-pointer items-center gap-2 rounded px-2 py-1 text-xs"
                                >
                                    <input
                                            type="checkbox"
                                            :checked="selectedPermissions.includes(perm.name)"
                                            @change="togglePermission(perm.name)"
                                            class="border-primary text-primary h-3.5 w-3.5 rounded"
                                    />
                                    <span class="truncate">{{ perm.name }}</span>
                                </label>
                            </div>
                        </div>

                        <div
                                v-if="filteredPermissions.length === 0"
                                class="text-muted-foreground py-4 text-center text-xs"
                        >
                            Tidak ada permission ditemukan
                        </div>
                    </div>
                </div>

                <DialogFooter class="mt-6 gap-2 sm:gap-0">
                    <AppButton
                            variant="outline"
                            size="sm"
                            type="button"
                            :left-icon="X"
                            :disabled="loading"
                            @click="emit('update:open', false)"
                    >
                        Batal
                    </AppButton>

                    <AppButton type="submit" size="sm" :loading="loading" :left-icon="Save">
                        {{ isEdit ? 'Simpan Perubahan' : 'Tambahkan Role' }}
                    </AppButton>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>