<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="flex items-center gap-2 mb-2">
      <span class="text-[11px] font-semibold text-[#aaa]">🎭 人物风格</span>
      <button @click="$emit('update:modelValue','none')" class="ml-auto px-1.5 py-px bg-transparent border border-[#333] rounded text-[8px] text-[#555] hover:text-[#e74c3c]">重置</button>
      <button v-if="user" @click="$emit('edit')" class="px-1.5 py-px bg-transparent border border-[#333] rounded text-[8px] text-[#555] hover:text-[#667eea]">＋</button>
    </div>
    <div class="grid grid-cols-6 gap-1">
      <div v-for="s in items" :key="s.id" @click="$emit('update:modelValue', s.id)"
        :class="['bg-[#25252d] border rounded-md p-1 cursor-pointer text-center transition-all group relative',
          modelValue===s.id ? 'border-[#667eea] bg-[#667eea15]' : 'border-[#333] hover:border-[#667eea66]',
          s._custom ? 'border-[#667eea44]! bg-[#667eea0a]!' : '']">
        <span v-if="s._custom" class="bg-[#667eea22] text-[#667eea] text-[7px] px-0.5 rounded absolute top-0.5 left-0.5">我的</span>
        <button v-if="s._custom && user" @click.stop="$emit('delete', s._id)" class="absolute top-0.5 right-0.5 w-3.5 h-3.5 bg-[#ef444488] rounded-full text-white text-[7px] hidden group-hover:flex items-center justify-center z-10">✕</button>
        <button v-if="user" @click.stop="$emit('edit', s)" class="absolute top-0.5 right-0.5 w-4 h-4 bg-transparent text-[#666] text-[9px] hidden group-hover:flex items-center justify-center rounded hover:text-[#667eea] hover:bg-[#667eea22] z-10" :style="s._custom?'right-5':''">✏️</button>
        <div class="w-full aspect-square rounded overflow-hidden mb-0.5">
          <img v-if="s.icon && !s.icon.startsWith('<')" :src="s.icon" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full bg-[#222] flex items-center justify-center text-lg">🎨</div>
        </div>
        <div class="text-[9px] font-semibold text-[#ccc]">{{ s.name }}</div>
        <div class="text-[7px] text-[#777]">{{ s._custom ? '自定义' : (s.desc||'') }}</div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue'
const props = defineProps({ modelValue: String, customPresets: Array, user: Object })
defineEmits(['update:modelValue', 'edit', 'delete'])
const defaults = [
  { id:'none', name:'通用', desc:'不使用风格模板' },
  { id:'film', name:'电影角色', desc:'暖灰·柔光' }, { id:'hard', name:'冷峻硬朗', desc:'深灰·冷调' },
  { id:'fantasy', name:'奇幻精灵', desc:'深蓝·魔幻' }, { id:'cyberpunk', name:'赛博朋克', desc:'钢蓝·霓虹' },
  { id:'warrior', name:'战士', desc:'深红·硬光' }, { id:'gufeng', name:'古风雅韵', desc:'水墨·柔光' },
  { id:'noir', name:'暗黑Noir', desc:'黑灰·侧光' }, { id:'sweet', name:'甜美清新', desc:'暖粉·柔光' },
  { id:'royal', name:'尊贵气场', desc:'金黑·顶光' }, { id:'street', name:'街头潮流', desc:'城市灰·硬光' },
  { id:'dream', name:'梦幻仙境', desc:'紫蓝·梦幻' }, { id:'scifi', name:'科技未来', desc:'冰蓝·冷光' },
  { id:'wasteland', name:'废土末日', desc:'尘土·破败' }, { id:'gothic', name:'暗黑哥特', desc:'紫黑·哥特' },
  { id:'jp', name:'日系校园', desc:'明亮·青春' }, { id:'victorian', name:'维多利亚', desc:'暗金·复古' },
  { id:'zen', name:'极简禅意', desc:'素白·极简' }
]
const items = computed(() => (props.customPresets && props.customPresets.length) ? props.customPresets : defaults)
</script>
