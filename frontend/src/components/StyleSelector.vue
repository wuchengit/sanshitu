<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="flex items-center gap-2 mb-2">
      <span class="text-[11px] font-semibold text-[#aaa]">🎭 人物风格</span>
      <button @click="$emit('update:modelValue','none')" class="ml-auto px-1.5 py-px bg-transparent border border-[#333] rounded text-[8px] text-[#555] hover:text-[#e74c3c]">重置</button>
    </div>
    <div class="grid grid-cols-6 gap-1">
      <div v-for="s in styles" :key="s.id" @click="$emit('update:modelValue', s.id)"
        :class="['bg-[#25252d] border rounded-md p-1 cursor-pointer text-center transition-all',
          modelValue===s.id ? 'border-[#667eea] bg-[#667eea15]' : 'border-[#333] hover:border-[#667eea66]']">
        <div class="w-full aspect-square rounded overflow-hidden mb-0.5" v-html="s.icon"></div>
        <div class="text-[9px] font-semibold text-[#ccc]">{{ s.name }}</div>
        <div class="text-[7px] text-[#777]">{{ s.desc }}</div>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({ modelValue: String })
defineEmits(['update:modelValue'])
const styles = [
  { id:'none', name:'通用', desc:'不使用风格模板', icon:'<div class="w-full h-full bg-[#222] flex items-center justify-center text-lg">🎨</div>' },
  { id:'film', name:'电影角色', desc:'暖灰·柔光', icon:'<div class="w-full h-full bg-[#2a2520] flex items-center justify-center text-lg">🎬</div>' },
  { id:'hard', name:'冷峻硬朗', desc:'深灰·冷调', icon:'<div class="w-full h-full bg-[#20252a] flex items-center justify-center text-lg">🧑</div>' },
  { id:'fantasy', name:'奇幻精灵', desc:'深蓝·魔幻', icon:'<div class="w-full h-full bg-[#1a2240] flex items-center justify-center text-lg">🧝</div>' },
  { id:'cyberpunk', name:'赛博朋克', desc:'钢蓝·霓虹', icon:'<div class="w-full h-full bg-[#1a2535] flex items-center justify-center text-lg">🤖</div>' },
  { id:'warrior', name:'战士', desc:'深红·硬光', icon:'<div class="w-full h-full bg-[#301a1a] flex items-center justify-center text-lg">⚔️</div>' },
  { id:'gufeng', name:'古风雅韵', desc:'水墨·柔光', icon:'<div class="w-full h-full bg-[#252a2a] flex items-center justify-center text-lg">🏯</div>' },
  { id:'noir', name:'暗黑Noir', desc:'黑灰·侧光', icon:'<div class="w-full h-full bg-[#151518] flex items-center justify-center text-lg">🕶️</div>' },
  { id:'sweet', name:'甜美清新', desc:'暖粉·柔光', icon:'<div class="w-full h-full bg-[#2a2030] flex items-center justify-center text-lg">🌸</div>' },
  { id:'royal', name:'尊贵气场', desc:'金黑·顶光', icon:'<div class="w-full h-full bg-[#252520] flex items-center justify-center text-lg">👑</div>' },
  { id:'street', name:'街头潮流', desc:'城市灰·硬光', icon:'<div class="w-full h-full bg-[#252a25] flex items-center justify-center text-lg">🔥</div>' },
  { id:'dream', name:'梦幻仙境', desc:'紫蓝·梦幻', icon:'<div class="w-full h-full bg-[#251a40] flex items-center justify-center text-lg">🌙</div>' },
  { id:'scifi', name:'科技未来', desc:'冰蓝·冷光', icon:'<div class="w-full h-full bg-[#1a2a3a] flex items-center justify-center text-lg">💠</div>' },
  { id:'wasteland', name:'废土末日', desc:'尘土·破败', icon:'<div class="w-full h-full bg-[#2a2515] flex items-center justify-center text-lg">🏜️</div>' },
  { id:'gothic', name:'暗黑哥特', desc:'紫黑·哥特', icon:'<div class="w-full h-full bg-[#201525] flex items-center justify-center text-lg">🦇</div>' },
  { id:'jp', name:'日系校园', desc:'明亮·青春', icon:'<div class="w-full h-full bg-[#253540] flex items-center justify-center text-lg">🎒</div>' },
  { id:'victorian', name:'维多利亚', desc:'暗金·复古', icon:'<div class="w-full h-full bg-[#2a2015] flex items-center justify-center text-lg">🎩</div>' },
  { id:'zen', name:'极简禅意', desc:'素白·极简', icon:'<div class="w-full h-full bg-[#252525] flex items-center justify-center text-lg">🧘</div>' }
]
</script>
