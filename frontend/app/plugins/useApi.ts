export default defineNuxtPlugin((nuxtApp) => {
    const config = useRuntimeConfig()

    const api = $fetch.create({
        baseURL: process.server
            ? (config.apiBaseServer as string)
            : (config.public.apiBaseClient as string),

        async onRequest({options}) {
            const headers = new Headers(options.headers as HeadersInit | undefined)

            if (!headers.has('accept')) {
                headers.set('accept', 'application/json')
            }

            const withAuth =
                (options as any).auth === undefined
                    ? true
                    : (options as any).auth

            delete (options as any).auth

            if (withAuth) {
                const token = await nuxtApp.runWithContext(() => useTrackingToken())

                if (token) {
                    headers.set('authorization', `Bearer ${token}`)
                }
            }

            options.headers = headers
        },
    })

    return {
        provide: {
            api,
        },
    }
})
