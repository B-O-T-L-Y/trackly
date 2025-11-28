[🏠 Main](../README.md)

`.docs/frontend.md`

# Frontend SPA (Nuxt 4)

The frontend is a `Nuxt 4` single-page application that displays today’s statistics and sends tracking events to the backend API.

## 1. Tech stack

- Nuxt 4
- Vue 3 Composition API
- Tailwind CSS (via `@nuxtjs/tailwindcss`)
- Native `useFetch` / `$fetch` wrappers for API calls

Entry points:

- `frontend/nuxt.config.ts`
- `frontend/app/pages/index.vue`
- `frontend/app/composables/useAnalytics.ts`
- `frontend/app/composables/useApiFetch.ts`
- `frontend/app/composables/useTrackingToken.ts`
- `frontend/app/plugins/useApi.ts`

## 2. Runtime config

`frontend/nuxt.config.ts`:

```typescript
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  runtimeConfig: {
    apiBaseServer: '',
    apiTokenPath: '',
    public: {
      apiBaseClient: '',
      statsPollInterval: '',
    }
  },

  modules: ['@nuxthq/tailwindcss'] // via @nuxtjs/tailwindcss
})
```

Values are injected from `.env`:

```dotenv
NUXT_API_BASE_SERVER=http://nginx/api
NUXT_API_TOKEN_PATH=/dev/token
NUXT_PUBLIC_API_BASE_CLIENT=http://localhost:8000/api
NUXT_PUBLIC_STATS_POLL_INTERVAL=5
```

## 3. API plugin (`useApi`)

`frontend/app/plugins/useApi.ts` creates a custom `$api` client on top of `$fetch`:

- Uses different base URLs on server and client:

```typescript
baseURL: process.server
  ? (config.apiBaseServer as string)
  : (config.public.apiBaseClient as string)
```

- Ensures `Accept: application/json` is always present. 
- Handles optional `auth` flag per request (default `true`). 
- When `auth` is enabled, gets a tracking token via `useTrackingToken` and sets:

```http request
Authorization: Bearer <token>
```
This centralises authentication and base URL handling for all frontend API calls.

## 4. Tracking token composable (useTrackingToken)

`frontend/app/composables/useTrackingToken.ts`:

  - Stores token in a Nuxt state named `trackingToken`. 
  - If it is already set, returns cached value. 
  - Otherwise calls internal Nuxt endpoint `/api/tracking-token`:

```typescript
const { token } = await $fetch<{ token: string }>('/api/tracking-token', {
  headers: { accept: 'application/json' },
});
```

The server route `frontend/server/api/tracking-token.get.ts`:

- Reads `apiBaseServer` and `apiTokenPath` from runtime config. 
- Calls the backend `/dev/token` endpoint. 
- Returns `{ token }` to the browser.

## 5. Analytics composable (`useAnalytics`)

`frontend/app/composables/useAnalytics.ts` encapsulates the logic of sending events.

### 5.1 Session management

A persistent session id is stored in `localStorage` under `trackly_session_id`:

```typescript
function getOrCreateSessionId() {
  if (typeof window === 'undefined') return ''

  let id = window.localStorage.getItem(SESSION_KEY)

  if (!id) {
    id = window.crypto?.randomUUID
      ? window.crypto.randomUUID()
      : Math.random().toString(36).slice(2)

    window.localStorage.setItem(SESSION_KEY, id)
  }

  return id
}
```

### 5.2 Sending an event

`sendEvent(type: EventType)`:

- Sets `sending` to `true`. 
- Generates a unique `idempotencyKey` (UUID or random string). 
- Calls `nuxtApp.$api('/v1/events', { method: 'POST', headers, body })` with:
  - `X-Idempotency-Key` header. 
  - JSON body: `{ type, ts, session_id }`. 
- On success, returns the parsed response (including `duplicate` flag). 
- On failure, logs error and sets a human-readable `error` string.

The composable returns:

```typescript
{
  sendEvent,   // async function
  sending,     // Ref<boolean>
  error,       // Ref<string | null>
}
```

## 6. Stats fetch wrapper (useApiFetch)

`frontend/app/composables/useApiFetch.ts` is a thin wrapper around Nuxt `useFetch`:

```typescript
return useFetch<T>(url, {
  ...options,
  watch: false,
  $fetch: nuxtApp.$api as typeof $fetch,
});
```

This allows components to use `useFetch` ergonomics with the configured `$api` client (correct base URL, auth, headers).

## 7. Index page UI

`frontend/app/pages/index.vue` implements the main UI.

### 7.1 Data and state

- Uses `useAnalytics()` to get `sendEvent`, `sending`, and `eventError`.
- Fetches today stats:

```typescript
const {
  data: statsResponse,
  pending: statsPending,
  error: statsFetchError,
  refresh: refreshStats,
} = await useApiFetch<{ data: Stats }>('/v1/stats/today')
```

- Derives `stats` from `statsResponse`, with safe defaults. 
- Disables buttons when `sending` is `true`. 
- Uses `config.public.statsPollInterval` for periodic stats refresh.

### 7.2 Toast messages

A simple toast implementation:

```typescript
const toastMessage = ref<string | null>(null)
const toastType = ref<'success' | 'error' | null>(null)
const toastTimeoutId = ref<ReturnType<typeof setTimeout> | null>(null)

function showToast(message: string, type: ToastType) {
  toastMessage.value = message
  toastType.value = type

  if (toastTimeoutId.value) clearTimeout(toastTimeoutId.value)

  toastTimeoutId.value = setTimeout(() => {
    toastTimeoutId.value = null
    toastMessage.value = null
    toastType.value = null
  }, 3000)
}
```

Errors from `eventError` and `statsError` are watched and displayed via `showToast`.

On unmount, any pending timeout and polling interval are cleared.

### 7.3 Polling

Every `statsPollInterval` seconds the page calls `refreshStats()` to update the counters without full reload.

## 8. Styling

Tailwind classes are used directly in the template:

- Buttons use semantic colors (blue, indigo, emerald) with hover and focus states. 
- Content is wrapped in a centered container with a card-like panel. 
- The layout supports dark mode via Tailwind `dark:` variants.

The theme is intentionally minimal so that the focus stays on the tracking behaviour rather than UI complexity.

[⬅ Preview](backend.md) | [Next ➡](postman.md)