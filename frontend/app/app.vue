<template>
  <div>
    <NuxtPage />
  </div>
</template>
<script>
definePageMeta({
  middleware: ['auth'] 
});
</script>