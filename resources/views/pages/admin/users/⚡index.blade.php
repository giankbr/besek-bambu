<?php

use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Users')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'role', except: '')]
    public string $roleFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter === 'admin', fn ($q) => $q->where('is_admin', true))
            ->when($this->roleFilter === 'customer', fn ($q) => $q->where('is_admin', false))
            ->latest()
            ->paginate(15);
    }

    public function toggleAdmin(int $id): void
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to update'),
                    text: __('You cannot change your own admin status.'),
                );
                return;
            }

            if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to update'),
                    text: __('At least one admin must remain.'),
                );
                return;
            }

            $user->update(['is_admin' => ! $user->is_admin]);

            Flux::toast(
                variant: 'success',
                text: $user->is_admin ? __('User promoted to admin.') : __('Admin role revoked.'),
            );
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: $e->getMessage(),
            );
        }
    }

    public function delete(int $id): void
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to delete'),
                    text: __('You cannot delete your own account.'),
                );
                return;
            }

            if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to delete'),
                    text: __('At least one admin must remain.'),
                );
                return;
            }

            $user->delete();

            Flux::toast(variant: 'success', text: __('User deleted.'));
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to delete'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Users') }}</flux:heading>
                <flux:subheading>{{ __('Manage admins and customers.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.users.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('New user') }}
            </flux:button>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search by name or email…') }}"
                class="max-w-sm"
            />
            <flux:select wire:model.live="roleFilter" class="max-w-xs">
                <flux:select.option value="">{{ __('All users') }}</flux:select.option>
                <flux:select.option value="admin">{{ __('Admins only') }}</flux:select.option>
                <flux:select.option value="customer">{{ __('Customers only') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Email') }}</flux:table.column>
                <flux:table.column>{{ __('Role') }}</flux:table.column>
                <flux:table.column>{{ __('Verified') }}</flux:table.column>
                <flux:table.column>{{ __('Joined') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar :name="$user->name" :initials="$user->initials()" size="sm" />
                                <div>
                                    <div class="font-medium">{{ $user->name }}</div>
                                    @if ($user->id === auth()->id())
                                        <flux:text size="sm" class="text-zinc-500">{{ __('You') }}</flux:text>
                                    @endif
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($user->is_admin)
                                <flux:badge color="emerald" size="sm">{{ __('Admin') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Customer') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($user->email_verified_at)
                                <flux:badge color="green" size="sm">{{ __('Verified') }}</flux:badge>
                            @else
                                <flux:badge color="amber" size="sm">{{ __('Pending') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $user->created_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    :icon="$user->is_admin ? 'shield-exclamation' : 'shield-check'"
                                    wire:click="toggleAdmin({{ $user->id }})"
                                    wire:confirm="{{ $user->is_admin ? __('Revoke admin role?') : __('Promote this user to admin?') }}"
                                >
                                    {{ $user->is_admin ? __('Revoke') : __('Promote') }}
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil-square"
                                    :href="route('admin.users.edit', $user)"
                                    wire:navigate
                                />
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="delete({{ $user->id }})"
                                    wire:confirm="{{ __('Delete this user permanently?') }}"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500">
                            {{ __('No users match your filters.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</section>
