export const useTrackingToken = async (): Promise<string | null> => {
    const state = useState<string | null>('trackingToken', () => null)

    if (state.value) {
        return state.value
    }

    try {
        const {token} = await $fetch<{ token: string }>('/api/tracking-token', {
            headers: {
                accept: 'application/json',
            },
        })

        state.value = token ?? null

        return state.value
    } catch (e) {
        console.error('Failed to fetch tracking token', e)

        return null
    }
}
