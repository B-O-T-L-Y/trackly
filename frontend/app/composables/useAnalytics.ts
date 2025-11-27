import type {EventType} from "~/types/events";

const SESSION_KEY = 'trackly_session_id'

function getOrCreateSessionId() {
    if (typeof window === 'undefined') {
        return ''
    }

    let id = window.localStorage.getItem(SESSION_KEY)

    if (!id) {
        if (window.crypto?.randomUUID) {
            id = window.crypto.randomUUID()
        } else {
            id = Math.random().toString(36).slice(2)
        }

        window.localStorage.setItem(SESSION_KEY, id)
    }

    return id
}

export function useAnalytics() {
    const loading = ref(false)
    const error = ref<string | null>(null)

    const sendEvent = async (type: EventType) => {
        loading.value = true
        error.value = null

        const sessionId = getOrCreateSessionId()

        const idempotencyKey =
            typeof window !== 'undefined' && window.crypto?.randomUUID
                ? window.crypto.randomUUID()
                : Math.random().toString(36).slice(2)

        try {
            await useApiFetch('/v1/events', {
                method: 'POST',
                headers: {
                    'X-Idempotency-Key': idempotencyKey,
                },
                body: {
                    type,
                    ts: new Date().toISOString(),
                    session_id: sessionId,
                }
            })
        } catch (e: any) {
            console.error('Failed to send event ', e)

            error.value = e?.data?.message || e.message || 'Failed to send event'
        } finally {
            loading.value = false
        }
    }

    return {
        sendEvent,
        loading,
        error,
    }
}