import {computed, onBeforeUnmount, onMounted, ref, watch,} from 'vue'
import type {PaginatedApiResponse, PaginationMeta} from '@/types'
import {debounce} from '@/utils/debounce'

export interface DataTableQuery {
    page?: number
    per_page?: number
    search?: string
    sort?: string
    direction?: 'asc' | 'desc'

    [key: string]: any
}

export interface DataTableOptions {
    initialPerPage?: number
    initialSort?: string
    initialDirection?: 'asc' | 'desc'
    debounceMs?: number
    autoFetch?: boolean
}

export function useDataTable<T>(
    fetcher: (
        params: DataTableQuery,
        signal?: AbortSignal
    ) => Promise<PaginatedApiResponse<T>>,
    options: DataTableOptions | number = {}
) {
    const config: DataTableOptions = typeof options === 'number'
        ? {initialPerPage: options}
        : options

    const {
        initialPerPage = 10,
        initialSort = undefined,
        initialDirection = undefined,
        debounceMs = 300,
        autoFetch = true,
    } = config

    // ============================================
    // STATE
    // ============================================
    const items = ref<T[]>([])
    const pagination = ref<PaginationMeta | null>(null)
    const page = ref(1)
    const perPage = ref<number | null>(initialPerPage)
    const search = ref('')
    const sort = ref<string | undefined>(initialSort)
    const direction = ref<'asc' | 'desc' | undefined>(initialDirection)
    const filters = ref<Record<string, any>>({})
    const loading = ref(false)
    const error = ref<string | null>(null)

    let controller: AbortController | null = null
    const isMounted = ref(false)
    const fetchCount = ref(0)

    // ============================================
    // FETCH DATA
    // ============================================
    const fetchData = async () => {
        try {
            controller?.abort()
            controller = new AbortController()
            loading.value = true
            error.value = null
            fetchCount.value++

            // 🔥 Format params untuk Spatie Query Builder
            const params: Record<string, any> = {
                page: page.value,
                per_page: perPage.value ?? undefined,
            }

            // Search → filter[name] (Spatie format)
            if (search.value) {
                params['filter[name]'] = search.value
            }

            // Filters → filter[field]
            Object.entries(filters.value).forEach(([key, value]) => {
                if (value !== undefined && value !== null && value !== 'all') {
                    params[`filter[${key}]`] = value
                }
            })

            // 🔥 Sort → Spatie format: sort=-name (desc) atau sort=name (asc)
            if (sort.value) {
                params['sort'] = direction.value === 'desc' ? `-${sort.value}` : sort.value
            }

            // console.log('🔍 fetchData params:', params)

            const response = await fetcher(params, controller.signal)

            items.value = response.data
            pagination.value = response.meta
        } catch (err: any) {
            if (err.name === 'CanceledError' || err.name === 'AbortError') return
            error.value = err?.response?.data?.message || 'Terjadi kesalahan saat memuat data.'
        } finally {
            loading.value = false
        }
    }
    const debouncedFetch = debounce(fetchData, debounceMs)

    // ============================================
    // FILTER METHODS
    // ============================================
    const setFilter = (key: string, value: any) => {
        // console.log('🔄 setFilter:', key, value)
        filters.value[key] = value
        page.value = 1
        if (isMounted.value) fetchData()
    }

    const setFilters = (newFilters: Record<string, any>) => {
        filters.value = {...filters.value, ...newFilters}
        page.value = 1
        if (isMounted.value) fetchData()
    }

    const removeFilter = (key: string) => {
        delete filters.value[key]
        page.value = 1
        if (isMounted.value) fetchData()
    }

    const resetFilters = () => {
        filters.value = {}
        page.value = 1
        if (isMounted.value) fetchData()
    }

    // ============================================
    // CHANGE METHODS
    // ============================================
    const changePage = (newPage: number) => {
        // console.log('🔄 changePage:', newPage)
        if (!pagination.value) return
        if (newPage < 1 || newPage > pagination.value.last_page) return
        page.value = newPage
        if (isMounted.value) fetchData()
    }

    const changePerPage = (value: number | null) => {
        // console.log('🔄 changePerPage:', value)
        perPage.value = value
        page.value = 1
        if (isMounted.value) fetchData()
    }

    const changeSorting = (column: string, dir: 'asc' | 'desc' | null) => {
        // console.log('🔄 changeSorting:', column, dir)
        if (!column || !dir) {
            sort.value = undefined
            direction.value = undefined
        } else {
            sort.value = column
            direction.value = dir
        }
        page.value = 1
        if (isMounted.value) fetchData()
    }

    const clearSorting = () => {
        sort.value = undefined
        direction.value = undefined
        page.value = 1
        if (isMounted.value) fetchData()
    }

    const setSearch = (value: string) => {
        search.value = value
        page.value = 1
        if (isMounted.value) debouncedFetch()
    }

    const clearSearch = () => {
        search.value = ''
        page.value = 1
        if (isMounted.value) debouncedFetch()
    }

    const refresh = () => fetchData()

    // ============================================
    // DERIVED STATE
    // ============================================
    const isInitialLoading = computed(() => loading.value && items.value.length === 0)
    const isRefetching = computed(() => loading.value && items.value.length > 0)
    const isEmpty = computed(() => !loading.value && items.value.length === 0)
    const hasError = computed(() => !!error.value)
    const totalItems = computed(() => pagination.value?.total || items.value.length)
    const currentPage = computed(() => pagination.value?.current_page || page.value)
    const totalPages = computed(() => pagination.value?.last_page || 1)
    const isFirstPage = computed(() => currentPage.value <= 1)
    const isLastPage = computed(() => currentPage.value >= totalPages.value)

    // ============================================
    // WATCHERS
    // ============================================
    watch(filters, () => {
        if (isMounted.value) fetchData()
    }, {deep: true})

    watch(search, () => {
        if (isMounted.value) {
            page.value = 1
            debouncedFetch()
        }
    })

    // ============================================
    // LIFECYCLE
    // ============================================
    onMounted(() => {
        isMounted.value = true
        if (autoFetch) {
            fetchData()
        }
    })

    onBeforeUnmount(() => {
        controller?.abort()
        debouncedFetch.cancel?.()
    })

    return {
        items,
        pagination,
        page,
        perPage,
        search,
        loading,
        error,
        sort,
        direction,
        filters,
        fetchData,
        refresh,
        fetchCount,
        setFilter,
        setFilters,
        removeFilter,
        resetFilters,
        changePage,
        changePerPage,
        changeSorting,
        clearSorting,
        setSearch,
        clearSearch,
        isInitialLoading,
        isRefetching,
        isEmpty,
        hasError,
        totalItems,
        currentPage,
        totalPages,
        isFirstPage,
        isLastPage,
    }
}