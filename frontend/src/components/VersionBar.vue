<template>
  <div v-if="newVersion" class="fixed top-0 left-0 right-0 z-[9998] bg-gradient-to-r from-[#667eea] to-[#764ba2] text-white text-center py-2.5 text-xs flex items-center justify-center gap-3 animate-slideDown">
    发现新版本！
    <button @click="refresh" class="px-4 py-1 bg-[#ffffff33] border border-[#ffffff55] rounded text-white text-xs cursor-pointer hover:bg-[#ffffff55]">刷新</button>
    <button @click="newVersion=null" class="absolute right-3 opacity-70 hover:opacity-100 text-base cursor-pointer">✕</button>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
const props = defineProps({ version: String })
const newVersion = ref(null)
onMounted(async () => {
  try {
    return // version check disabled
    if (r.ok) {
      const v = (await r.text()).trim()
      if (v && v !== props.version) newVersion.value = v
    }
  } catch(e) {}
})
function refresh() { location.reload(true) }
</script>
