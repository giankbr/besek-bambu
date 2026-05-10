<?php

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Activity log')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'event', except: '')]
    public string $eventFilter = '';

    #[Url(as: 'subject', except: '')]
    public string $subjectFilter = '';

    #[Url(as: 'user', except: '')]
    public string $userFilter = '';

    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'to', except: '')]
    public string $dateTo = '';

    public ?int $previewId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedEventFilter(): void { $this->resetPage(); }
    public function updatedSubjectFilter(): void { $this->resetPage(); }
    public function updatedUserFilter(): void { $this->resetPage(); }
    public function updatedDateFrom(): void { $this->resetPage(); }
    public function updatedDateTo(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset('search', 'eventFilter', 'subjectFilter', 'userFilter', 'dateFrom', 'dateTo');
        $this->resetPage();
    }

    public function preview(int $id): void
    {
        $this->previewId = $id;
    }

    public function closePreview(): void
    {
        $this->previewId = null;
    }

    #[Computed]
    public function logs()
    {
        return ActivityLog::query()
            ->with('user:id,name,email')
            ->when($this->search !== '', fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->eventFilter !== '', fn ($q) => $q->where('event', $this->eventFilter))
            ->when($this->subjectFilter !== '', fn ($q) => $q->where('subject_type', $this->subjectFilter))
            ->when($this->userFilter !== '', fn ($q) => $q->where('user_id', $this->userFilter))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function eventOptions(): array
    {
        return ActivityLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->all();
    }

    #[Computed]
    public function subjectOptions(): array
    {
        return ActivityLog::query()
            ->select('subject_type')
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->all();
    }

    #[Computed]
    public function userOptions()
    {
        return User::query()
            ->whereIn('id', ActivityLog::query()->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function previewLog(): ?ActivityLog
    {
        if (! $this->previewId) {
            return null;
        }

        return ActivityLog::with('user:id,name,email')->find($this->previewId);
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('Activity log') }}</flux:heading>
            <flux:subheading>{{ __('Audit trail of admin actions across the store.') }}</flux:subheading>
        </div>

        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-6">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search…') }}"
                class="lg:col-span-2"
            />
            <flux:select wire:model.live="eventFilter">
                <flux:select.option value="">{{ __('All events') }}</flux:select.option>
                @foreach ($this->eventOptions as $event)
                    <flux:select.option value="{{ $event }}">{{ ucfirst($event) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="subjectFilter">
                <flux:select.option value="">{{ __('All subjects') }}</flux:select.option>
                @foreach ($this->subjectOptions as $subject)
                    <flux:select.option value="{{ $subject }}">{{ class_basename($subject) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="userFilter">
                <flux:select.option value="">{{ __('All users') }}</flux:select.option>
                @foreach ($this->userOptions as $u)
                    <flux:select.option value="{{ $u->id }}">{{ $u->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input type="date" wire:model.live="dateFrom" :placeholder="__('From')" />
            <flux:input type="date" wire:model.live="dateTo" :placeholder="__('To')" />
        </div>

        @if ($search !== '' || $eventFilter !== '' || $subjectFilter !== '' || $userFilter !== '' || $dateFrom !== '' || $dateTo !== '')
            <div>
                <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="clearFilters">{{ __('Clear filters') }}</flux:button>
            </div>
        @endif

        <flux:table :paginate="$this->logs">
            <flux:table.columns>
                <flux:table.column>{{ __('When') }}</flux:table.column>
                <flux:table.column>{{ __('Actor') }}</flux:table.column>
                <flux:table.column>{{ __('Event') }}</flux:table.column>
                <flux:table.column>{{ __('Subject') }}</flux:table.column>
                <flux:table.column>{{ __('Description') }}</flux:table.column>
                <flux:table.column>{{ __('IP') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->logs as $log)
                    <flux:table.row :key="$log->id">
                        <flux:table.cell>
                            <div>{{ $log->created_at->format('M d, Y') }}</div>
                            <flux:text size="sm" class="text-zinc-500">{{ $log->created_at->format('H:i') }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($log->user)
                                <div class="font-medium">{{ $log->user->name }}</div>
                                <flux:text size="sm" class="text-zinc-500">{{ $log->user->email }}</flux:text>
                            @else
                                <flux:text class="text-zinc-500">{{ __('System') }}</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $eventColor = match ($log->event) {
                                    'created' => 'green',
                                    'updated' => 'blue',
                                    'deleted' => 'red',
                                    default => 'zinc',
                                };
                            @endphp
                            <flux:badge :color="$eventColor" size="sm">{{ ucfirst($log->event) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($log->subject_type)
                                <div class="font-medium">{{ class_basename($log->subject_type) }}</div>
                                <flux:text size="sm" class="text-zinc-500">#{{ $log->subject_id }}</flux:text>
                            @else
                                <flux:text class="text-zinc-500">—</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $log->description }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:text size="sm" class="font-mono text-zinc-500">{{ $log->ip_address ?? '—' }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if (! empty($log->properties))
                                <flux:button size="sm" variant="ghost" icon="eye" wire:click="preview({{ $log->id }})">
                                    {{ __('Details') }}
                                </flux:button>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-500">
                            {{ __('No activity yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($this->previewLog)
            <flux:modal
                name="activity-preview"
                :show="true"
                x-on:close="$wire.closePreview()"
                class="md:w-2xl"
            >
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg">{{ $this->previewLog->description }}</flux:heading>
                        <flux:subheading>{{ $this->previewLog->created_at->format('M d, Y · H:i') }} · {{ $this->previewLog->user?->name ?? __('System') }}</flux:subheading>
                    </div>

                    @php
                        $props = $this->previewLog->properties ?? [];
                        $hasDiff = isset($props['old']) && isset($props['new']);
                    @endphp

                    @if ($hasDiff)
                        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <table class="w-full text-sm">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium">{{ __('Field') }}</th>
                                        <th class="px-3 py-2 text-left font-medium">{{ __('Before') }}</th>
                                        <th class="px-3 py-2 text-left font-medium">{{ __('After') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach ($props['new'] as $key => $newVal)
                                        @php
                                            $oldVal = $props['old'][$key] ?? null;
                                        @endphp
                                        <tr>
                                            <td class="px-3 py-2 font-mono text-xs text-zinc-500">{{ $key }}</td>
                                            <td class="px-3 py-2 font-mono text-xs">{{ is_scalar($oldVal) || is_null($oldVal) ? (string) ($oldVal ?? '—') : json_encode($oldVal) }}</td>
                                            <td class="px-3 py-2 font-mono text-xs">{{ is_scalar($newVal) || is_null($newVal) ? (string) ($newVal ?? '—') : json_encode($newVal) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <pre class="overflow-x-auto rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-xs dark:border-zinc-700 dark:bg-zinc-900">{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @endif

                    <div class="flex items-center justify-end">
                        <flux:button variant="ghost" wire:click="closePreview">{{ __('Close') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </div>
</section>
