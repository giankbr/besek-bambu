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

    public ?int $toggleAdminUserId = null;

    public ?int $deletingUserId = null;

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

    #[Computed]
    public function toggleAdminTarget(): ?User
    {
        return $this->toggleAdminUserId
            ? User::find($this->toggleAdminUserId)
            : null;
    }

    public function confirmToggleAdmin(int $id): void
    {
        $this->toggleAdminUserId = $id;
        Flux::modal('toggle-admin-user')->show();
    }

    public function applyToggleAdmin(): void
    {
        if (! $this->toggleAdminUserId) {
            return;
        }

        $id = $this->toggleAdminUserId;

        try {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                Flux::modal('toggle-admin-user')->close();
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to update'),
                    text: __('You cannot change your own admin status.'),
                );

                return;
            }

            if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
                Flux::modal('toggle-admin-user')->close();
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to update'),
                    text: __('At least one admin must remain.'),
                );

                return;
            }

            // is_admin is not mass assignable.
            $user->is_admin = ! $user->is_admin;
            $user->save();

            Flux::toast(
                variant: 'success',
                text: $user->is_admin ? __('User promoted to admin.') : __('Admin role revoked.'),
            );

            $this->toggleAdminUserId = null;
            Flux::modal('toggle-admin-user')->close();
            unset($this->toggleAdminTarget, $this->users);
        } catch (\Throwable $e) {
            Flux::modal('toggle-admin-user')->close();
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: $e->getMessage(),
            );
        }
    }

    public function confirmDeleteUser(int $id): void
    {
        $this->deletingUserId = $id;
        Flux::modal('delete-user')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingUserId) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to delete'),
                text: __('No user selected.'),
            );

            return;
        }

        try {
            $user = User::findOrFail($this->deletingUserId);

            if ($user->id === auth()->id()) {
                Flux::modal('delete-user')->close();
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to delete'),
                    text: __('You cannot delete your own account.'),
                );

                return;
            }

            if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
                Flux::modal('delete-user')->close();
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to delete'),
                    text: __('At least one admin must remain.'),
                );

                return;
            }

            $user->delete();

            $this->deletingUserId = null;
            Flux::modal('delete-user')->close();
            Flux::toast(variant: 'success', text: __('User deleted.'));
            unset($this->users);
        } catch (\Throwable $e) {
            Flux::modal('delete-user')->close();
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

        {{-- Mobile cards --}}
        <div class="flex flex-col gap-2 md:hidden">
            @forelse ($this->users as $user)
                <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        <flux:avatar :name="$user->name" :initials="$user->initials()" size="sm" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate font-medium">{{ $user->name }}@if ($user->id === auth()->id()) <span class="text-xs text-zinc-400">({{ __('You') }})</span>@endif</div>
                            <div class="truncate text-xs text-zinc-500">{{ $user->email }}</div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            @if ($user->is_admin)
                                <flux:badge color="emerald" size="sm">{{ __('Admin') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Customer') }}</flux:badge>
                            @endif
                            @if ($user->email_verified_at)
                                <flux:badge color="green" size="sm">{{ __('Verified') }}</flux:badge>
                            @else
                                <flux:badge color="amber" size="sm">{{ __('Pending') }}</flux:badge>
                            @endif
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-zinc-500">{{ $user->created_at->diffForHumans() }}</span>
                        <div class="flex gap-1">
                            <flux:button size="sm" variant="ghost" :icon="$user->is_admin ? 'shield-exclamation' : 'shield-check'" wire:click="confirmToggleAdmin({{ $user->id }})" />
                            <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.users.edit', $user)" wire:navigate />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDeleteUser({{ $user->id }})" />
                        </div>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-zinc-500">{{ __('No users match your filters.') }}</p>
            @endforelse
            {{ $this->users->links() }}
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block">
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
                                    wire:click="confirmToggleAdmin({{ $user->id }})"
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
                                    wire:click="confirmDeleteUser({{ $user->id }})"
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
    </div>

    <flux:modal name="toggle-admin-user" class="md:w-96">
        <div class="space-y-6">
            <div>
                @if ($this->toggleAdminTarget?->is_admin)
                    <flux:heading size="lg">{{ __('Revoke admin role?') }}</flux:heading>
                    <flux:subheading>
                        {{ __(':name will no longer have admin access.', ['name' => $this->toggleAdminTarget->name]) }}
                    </flux:subheading>
                @else
                    <flux:heading size="lg">{{ __('Promote this user to admin?') }}</flux:heading>
                    <flux:subheading>
                        {{ __(':name will be able to access the admin dashboard.', ['name' => $this->toggleAdminTarget?->name ?? '']) }}
                    </flux:subheading>
                @endif
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button
                    variant="primary"
                    wire:click="applyToggleAdmin"
                    wire:loading.attr="disabled"
                >
                    {{ $this->toggleAdminTarget?->is_admin ? __('Revoke') : __('Promote') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <x-admin.confirm-modal
        name="delete-user"
        :title="__('Delete this user permanently?')"
        :description="__('This action cannot be undone.')"
        action="delete"
    />
</section>
