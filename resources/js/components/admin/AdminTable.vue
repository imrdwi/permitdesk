<script setup lang="ts">
defineProps<{
    empty?: boolean;
    emptyText?: string;
}>();
</script>

<template>
    <div class="overflow-x-auto">
        <table
            class="w-full border border-[#ddd] text-left text-[13px] [&_tbody_tr:hover_td]:bg-[#f1f5f9] [&_td]:border [&_td]:border-[#e5e5e5] [&_td]:bg-[#f8fafc] [&_td]:p-2 [&_th]:border-b [&_th]:border-[#e5e5e5] [&_th]:bg-[#f1f5f9] [&_th]:p-2 [&_th]:font-bold [&_th]:text-[#56688a]"
        >
            <thead>
                <slot name="head" />
            </thead>
            <tbody>
                <slot />
                <tr v-if="empty">
                    <td colspan="100" class="text-center text-muted-foreground">
                        {{ emptyText ?? 'Nothing here yet.' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
