<flux:menu.separator />

<div class="flex items-center justify-between gap-3 px-2 py-2" x-data>
    <div class="flex items-center gap-2 text-sm font-medium text-zinc-800 dark:text-white">
        <flux:icon icon="moon" variant="mini" class="text-zinc-400 dark:text-white/60" />
        {{ __('Dark mode') }}
    </div>
    <flux:switch x-model="$flux.dark" />
</div>
