import type {UseFetchOptions} from 'nuxt/app'

export function useApiFetch<T>(
    url: string | (() => string),
    options: UseFetchOptions<T> & { auth?: boolean } = {}
) {
    const nuxtApp = useNuxtApp()

    return useFetch<T>(url, {
        ...options,
        watch: false,
        $fetch: nuxtApp.$api as typeof $fetch,
    })
}
