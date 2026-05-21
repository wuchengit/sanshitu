<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="text-[11px] font-semibold text-[#aaa] mb-2">👤 人物属性</div>
    <div class="grid grid-cols-4 gap-1.5 mb-1.5">
      <select v-model="gender" class="bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-[10px] text-[#e8e8ed]">
        <option value="">性别不限</option><option>女</option><option>男</option></select>
      <select v-model="race" class="bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-[10px] text-[#e8e8ed]">
        <option value="">人种不限</option><option>东亚</option><option>白人/欧洲</option><option>黑人/非洲</option><option>南亚/印度</option><option>中东</option><option>拉丁裔</option><option>东南亚</option></select>
      <select v-model="age" class="bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-[10px] text-[#e8e8ed]">
        <option value="">年龄不限</option><option>小孩 6-10</option><option>年轻 20-25</option><option>成熟 26-35</option><option>中年 36-45</option><option>老者 60-80</option></select>
      <select v-model="height" class="bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-[10px] text-[#e8e8ed]">
        <option value="">身高不限</option><option>高</option><option>中等</option><option>娇小</option></select>
    </div>
    <div class="grid grid-cols-2 gap-1.5">
      <select v-model="job" class="bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-[10px] text-[#e8e8ed]">
        <option value="">👔 职业不限</option><option>⚔️ 战士</option><option>🔮 法师</option><option>🗡️ 刺客</option><option>🏹 猎人</option><option>✝️ 牧师</option><option>🛡️ 骑士</option><option>🐉 龙骑士</option><option>🦾 机甲驾驶员</option></select>
      <select v-model="era" class="bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-[10px] text-[#e8e8ed]">
        <option value="">⏳ 年代不限</option><option>🪨 远古石器</option><option>🏛️ 古埃及</option><option>🏰 中世纪</option><option>🐉 大唐盛世</option><option>🌆 赛博时代</option><option>🛸 太空科幻</option></select>
    </div>
  </div>
</template>
<script setup>
const gender = defineModel('gender')
const race = defineModel('race')
const age = defineModel('age')
const height = defineModel('height')
const job = defineModel('job')
const era = defineModel('era')
</script>
