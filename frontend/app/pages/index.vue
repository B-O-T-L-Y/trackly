<script setup lang="ts">
import type {EventType} from '~/types/events'
import type {Stats} from "~/types/stats";
import type {ToastType} from "~/types/toast-type";

const {sendEvent, sending, error: eventError} = useAnalytics()
const config = useRuntimeConfig()

const {
  data: statsResponse,
  pending: statsPending,
  error: statsFetchError,
  refresh: refreshStats,
} = await useApiFetch<{ data: Stats }>('/v1/stats/today')

const stats = computed<Stats>(() => statsResponse.value?.data ?? {
  date: '',
  counts: {
    page_view: 0,
    cta_click: 0,
    form_submit: 0,
  },
  total: 0,
})

const buttonsDisabled = computed(() => statsPending.value || sending.value)

const initialStateLoaded = computed(() => Boolean(statsResponse.value))

const statusMessage = computed(() => {
  if (!initialStateLoaded.value && statsPending.value) {
    return 'Loading status...'
  }

  if (sending.value) {
    return 'Sending event..'
  }

  return ''
})

const toastMessage = ref<string | null>(null)
const toastType = ref<ToastType | null>(null)

function showToast(message: string, type: ToastType) {
  toastMessage.value = message
  toastType.value = type
  setTimeout(() => {
    toastMessage.value = null
    toastType.value = null
  }, 3000)
}

const statsError = computed<string | null>(() => {
  const err = statsFetchError.value as any
  if (!err) return null
  return err?.data?.message || err?.message || 'Failed to fetch stats.'
})

watch(eventError, (err) => {
  if (err) showToast(err, 'error')
})

watch(statsError, (err) => {
  if (err) showToast(err, 'error')
})

async function handleClick(type: EventType) {
  const result = await sendEvent(type)

  if (!result) return

  const duplicate = result?.duplicate

  showToast(
      duplicate ? 'Duplicate event ignored.' : 'Event recorded successfully.',
      'success',
  )

  await refreshStats()
}

let intervalId: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  intervalId = setInterval(() => {
    refreshStats()
  }, Number(config.public.statsPollInterval) * 1000)
})

onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId)
})

useHead({
  title: 'Dashboard',
})
</script>

<template>
  <div
      v-if="toastMessage"
      class="fixed top-4 right-4 z-50 flex items-center w-full max-w-xs p-4 rounded-lg shadow"
      :class="toastType === 'success'
      ? 'text-green-800 bg-green-50 dark:text-green-200 dark:bg-green-800'
      : 'text-red-800 bg-red-50 dark:text-red-200 dark:bg-red-800'"
      role="alert"
  >
    <p class="flex-1 text-sm font-medium">{{ toastMessage }}</p>
  </div>

  <main class="container mx-auto max-w-3xl p-6">
    <section class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Trackly - Event Tracking Demo</h1>
      <p class="mb-6 text-gray-700 dark:text-gray-300">
        Press one of the buttons to send an event and view today’s statistics.
      </p>

      <div class="flex flex-wrap gap-3 mb-6">
        <button
            :disabled="buttonsDisabled"
            @click="handleClick('page_view')"
            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
        >
          Page View
        </button>
        <button
            :disabled="buttonsDisabled"
            @click="handleClick('cta_click')"
            class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:focus:ring-indigo-800"
        >
          CTA Click
        </button>
        <button
            :disabled="buttonsDisabled"
            @click="handleClick('form_submit')"
            class="text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-emerald-500 dark:hover:bg-emerald-600 dark:focus:ring-emerald-800"
        >
          Form Submit
        </button>
      </div>

      <div class="mb-4 h-5 flex items-center">
        <p
            :class="statusMessage ? 'opacity-100' : 'opacity-0'"
            class="text-sm text-gray-500 dark:text-gray-400 transition-opacity duration-150"
        >
          {{ statusMessage }}
        </p>
      </div>

      <section class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Today Stats</h2>
        <p class="mb-4 text-gray-600 dark:text-gray-300">
          Date: <span class="font-medium">{{ stats.date || '-' }}</span>
        </p>
        <ul class="space-y-2 text-gray-700 dark:text-gray-200">
          <li>Page View: <span class="font-semibold">{{ stats.counts.page_view }}</span></li>
          <li>CTA Click: <span class="font-semibold">{{ stats.counts.cta_click }}</span></li>
          <li>Form Submit: <span class="font-semibold">{{ stats.counts.form_submit }}</span></li>
        </ul>
        <p class="mt-4 text-gray-700 dark:text-gray-200">
          Total: <span class="font-bold">{{ stats.total }}</span>
        </p>
      </section>

      <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
        Polling every {{ config.public.statsPollInterval }} seconds.
      </p>
    </section>
  </main>
</template>
