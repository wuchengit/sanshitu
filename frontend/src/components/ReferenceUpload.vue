<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="text-[11px] font-semibold text-[#aaa] mb-2">🖼️ 参考图</div>
    <div class="flex gap-1 flex-wrap">
      <div v-for="(img,i) in images" :key="i" class="w-11 h-11 bg-[#25252d] border border-[#333] rounded relative overflow-hidden">
        <img :src="img" class="w-full h-full object-cover" />
        <button @click="remove(i)" class="absolute top-px right-px w-3.5 h-3.5 bg-[#ef4444cc] rounded-full text-white text-[7px] flex items-center justify-center">✕</button>
      </div>
      <label v-if="images.length < 6" class="w-11 h-11 bg-[#25252d] border border-dashed border-[#444] rounded cursor-pointer flex items-center justify-center text-base text-[#555] hover:border-[#667eea66] hover:text-[#667eea]">
        +
        <input type="file" accept="image/*" hidden @change="onFile" multiple />
      </label>
    </div>
  </div>
</template>
<script setup>
const images = defineModel()

function onFile(e) {
  const files = e.target.files
  for (const f of files) {
    if (images.value.length >= 6) break
    const reader = new FileReader()
    reader.onload = () => images.value.push(reader.result)
    reader.readAsDataURL(f)
  }
  e.target.value = ''
}
function remove(i) { images.value.splice(i, 1) }
</script>
