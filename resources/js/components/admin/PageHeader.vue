<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <!-- Atlant's page title: a large light heading above the panel content. -->
    <div
        class="flex flex-wrap items-start justify-between gap-4 border-b border-[#e5e5e5] pb-3"
    >
        <div class="space-y-1">
            <h1 class="text-[26px] leading-[30px] font-normal">{{ title }}</h1>
            <p v-if="description" class="text-[13px] text-muted-foreground">
                {{ description }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <slot />
        </div>
    </div>
</template>
