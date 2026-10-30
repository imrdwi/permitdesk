<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut } from '@lucide/vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { logout } from '@/routes';
</script>

<template>
    <!-- Atlant's message box: asks before signing out. -->
    <Dialog>
        <DialogTrigger
            class="flex h-[50px] items-center gap-2 px-4 text-[13px] text-[#444] hover:bg-[#f5f5f5]"
        >
            <LogOut class="size-4" /> Log out
        </DialogTrigger>
        <DialogContent
            class="max-w-none rounded-none border-0 bg-black/90 py-6 text-white shadow-none sm:max-w-none"
        >
            <div class="mx-auto w-full max-w-2xl">
                <DialogTitle class="text-3xl font-light text-white">
                    <LogOut class="inline size-7 align-[-3px]" />
                    <strong class="font-semibold"> Log out</strong>?
                </DialogTitle>
                <DialogDescription class="mt-3 space-y-2 text-sm text-white/80">
                    <span class="block">Are you sure you want to log out?</span>
                    <span class="block">
                        Press No to keep working. Press Yes to sign out.
                    </span>
                </DialogDescription>
                <DialogFooter class="mt-6">
                    <Link
                        :href="logout()"
                        as="button"
                        class="bg-[#95b75d] px-6 py-2.5 text-base text-[#1f2329] hover:bg-[#85a64f]"
                        data-test="logout-button"
                        @click="router.flushAll()"
                        >Yes</Link
                    >
                    <DialogClose
                        class="border border-[#ddd] bg-white px-6 py-2.5 text-base text-[#333] hover:bg-[#f5f5f5]"
                        >No</DialogClose
                    >
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>
