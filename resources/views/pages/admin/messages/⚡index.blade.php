<?php

use App\Models\ContactMessage;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Messages')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'state', except: '')]
    public string $stateFilter = '';

    public ?int $openedId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStateFilter(): void { $this->resetPage(); }

    #[Computed]
    public function messages()
    {
        return ContactMessage::query()
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('subject', 'like', "%{$this->search}%")
                      ->orWhere('message', 'like', "%{$this->search}%");
                });
            })
            ->when($this->stateFilter === 'unread', fn ($q) => $q->where('is_read', false))
            ->when($this->stateFilter === 'read', fn ($q) => $q->where('is_read', true))
            ->latest()
            ->paginate(15);
    }

    public function open(int $id): void
    {
        $this->openedId = $id;
        ContactMessage::where('id', $id)->where('is_read', false)->update(['is_read' => true]);
    }

    public function delete(int $id): void
    {
        ContactMessage::where('id', $id)->delete();
        if ($this->openedId === $id) {
            $this->openedId = null;
        }
        Flux::toast(variant: 'success', text: __('Message deleted.'));
    }

    #[Computed]
    public function opened()
    {
        return $this->openedId ? ContactMessage::find($this->openedId) : null;
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('Messages') }}</flux:heading>
            <flux:subheading>{{ __('Customer enquiries from the contact form.') }}</flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search messages…') }}"
                class="max-w-sm"
            />
            <flux:select wire:model.live="stateFilter" class="max-w-xs">
                <flux:select.option value="">{{ __('All') }}</flux:select.option>
                <flux:select.option value="unread">{{ __('Unread') }}</flux:select.option>
                <flux:select.option value="read">{{ __('Read') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <flux:table :paginate="$this->messages">
                    <flux:table.columns>
                        <flux:table.column>{{ __('From') }}</flux:table.column>
                        <flux:table.column>{{ __('Subject') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Received') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->messages as $message)
                            <flux:table.row :key="$message->id">
                                <flux:table.cell>
                                    <div class="font-medium">{{ $message->name }}</div>
                                    <flux:text size="sm" class="text-zinc-500">{{ $message->email }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>{{ $message->subject }}</flux:table.cell>
                                <flux:table.cell>
                                    @if ($message->is_read)
                                        <flux:badge color="zinc" size="sm">{{ __('Read') }}</flux:badge>
                                    @else
                                        <flux:badge color="blue" size="sm">{{ __('New') }}</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>{{ $message->created_at->diffForHumans() }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-1">
                                        <flux:button size="sm" variant="ghost" icon="eye" wire:click="open({{ $message->id }})">
                                            {{ __('View') }}
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            wire:click="delete({{ $message->id }})"
                                            wire:confirm="{{ __('Delete this message?') }}"
                                        />
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center text-zinc-500">
                                    {{ __('No messages yet.') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>

            <div>
                @if ($this->opened)
                    <flux:card>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <flux:heading size="lg">{{ $this->opened->subject }}</flux:heading>
                                <flux:subheading>{{ $this->opened->created_at->format('M d, Y · H:i') }}</flux:subheading>
                            </div>
                            <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="$set('openedId', null)" />
                        </div>
                        <flux:separator class="my-3" />
                        <flux:text class="text-zinc-500">{{ __('From') }}</flux:text>
                        <flux:text>{{ $this->opened->name }}</flux:text>
                        <a href="mailto:{{ $this->opened->email }}" class="text-sm text-blue-600 hover:underline">{{ $this->opened->email }}</a>
                        <flux:separator class="my-3" />
                        <flux:text class="whitespace-pre-line">{{ $this->opened->message }}</flux:text>
                        <div class="mt-4">
                            <flux:button variant="primary" :href="'mailto:' . $this->opened->email . '?subject=Re: ' . urlencode($this->opened->subject)">
                                {{ __('Reply via email') }}
                            </flux:button>
                        </div>
                    </flux:card>
                @else
                    <flux:card>
                        <flux:text class="text-zinc-500 text-center">{{ __('Select a message to view details.') }}</flux:text>
                    </flux:card>
                @endif
            </div>
        </div>
    </div>
</section>
