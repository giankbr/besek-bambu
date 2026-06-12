<?php

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component {
    #[Modelable]
    public string $value = '';

    public ?string $label = null;

    public ?string $description = null;

    public function mount(?string $label = null, ?string $description = null): void
    {
        $this->label = $label;
        $this->description = $description;
    }
}; ?>

<div class="grid gap-2">
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div wire:ignore class="sun-editor-wrapper overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div wire:ref="editor"></div>
    </div>

    @if ($description)
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $description }}</flux:text>
    @endif
</div>

@script
<script>
    let editor = null;

    const boot = () => {
        if (! window.createRichTextEditor) {
            return;
        }

        if (editor) {
            editor.destroy();
            editor = null;
        }

        editor = window.createRichTextEditor($refs.editor, {
            initialContent: $wire.value,
            onChange: (contents) => {
                $wire.value = contents;
            },
        });
    };

    boot();

    $wire.$watch('value', (value) => {
        if (! editor) {
            return;
        }

        const current = editor.getContents();

        if (current !== value) {
            editor.setContents(value || '');
        }
    });
</script>
@endscript
