<script setup lang="ts">
useHead({
  titleTemplate: (titleChunk) => titleChunk
      ? `${titleChunk} - Trackly`
      : 'Trackly - Event tracking Demo',
  meta: [
    {
      name: 'description',
      content: 'Trackly - minimal event tracking demo (page views, CTA clicks, form submits) with today stats.',
    },
  ],
})
</script>

<template>
  <div>
    <slot/>
  </div>
</template>

<style scoped></style>
