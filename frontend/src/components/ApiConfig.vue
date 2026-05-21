<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="text-[11px] font-semibold text-[#aaa] mb-2">🔑 API 配置</div>
    <input v-model="key" placeholder="自定义Key（可选）" class="w-full bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-xs text-[#e8e8ed] focus:border-[#667eea] outline-none" />
 <div class="grid grid-cols-3 gap-1.5 mt-2">
  <div><label class="text-[10px] text-[#999]">类型</label>
   <select v-model="model" class="w-full bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-xs text-[#e8e8ed]">
    <option>gpt-image-2</option><option>gpt-image-2-vip</option></select></div>
  <div><label class="text-[10px] text-[#999]">比例</label>
   <select v-model="ratio" class="w-full bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-xs text-[#e8e8ed]">
    <option>9:16 竖屏</option><option>16:9 横屏</option><option>1:1 方形</option><option>4:3</option><option>3:4</option>
    <option>3:2</option><option>2:3</option><option>5:4</option><option>4:5</option><option>21:9 超宽</option>
    <option>9:21 超长</option><option>2:1</option><option>1:2</option><option>3:1</option><option>1:3</option></select></div>
  <div><label class="text-[10px] text-[#999]">分辨率</label>
   <select v-model="resolution" class="w-full bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-xs text-[#e8e8ed]">
    <option>1K (自动按比例)</option><option v-if="model==='gpt-image-2-vip'">2K</option><option v-if="model==='gpt-image-2-vip'">4K</option></select></div>
 </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
const model = defineModel('model')
const ratio = defineModel('ratio')
const resolution = defineModel('resolution')
const key = defineModel('key')
</script>
