<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit user')] class extends Component {
    public User $user;

    public string $name = '';
    public string $email = '';
    public bool $is_admin = false;
    public bool $verified = false;

    public string $password = '';
    public string $password_confirmation = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_admin = (bool) $user->is_admin;
        $this->verified = $user->email_verified_at !== null;
    }

    public function save(): void
    {
        try {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
                'is_admin' => ['boolean'],
                'verified' => ['boolean'],
            ]);

            if ($this->user->id === auth()->id() && ! $validated['is_admin']) {
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to save'),
                    text: __('You cannot revoke your own admin access.'),
                );
                return;
            }

            if ($this->user->is_admin && ! $validated['is_admin'] && User::where('is_admin', true)->count() <= 1) {
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to save'),
                    text: __('At least one admin must remain.'),
                );
                return;
            }

            $this->user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            // is_admin is not mass assignable.
            $this->user->is_admin = (bool) $validated['is_admin'];

            if ($this->user->isDirty('email') && ! $this->verified) {
                $this->user->email_verified_at = null;
            } elseif ($this->verified && ! $this->user->email_verified_at) {
                $this->user->email_verified_at = now();
            } elseif (! $this->verified) {
                $this->user->email_verified_at = null;
            }

            $this->user->save();

            Flux::toast(variant: 'success', text: __('User updated.'));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to save'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form for errors.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to save'),
                text: $e->getMessage(),
            );
        }
    }

    public function resetPassword(): void
    {
        try {
            $this->validate([
                'password' => ['required', 'string', 'confirmed', Password::default()],
            ]);

            $this->user->update(['password' => $this->password]);

            $this->reset('password', 'password_confirmation');

            Flux::toast(variant: 'success', text: __('Password reset.'));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to reset password'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form for errors.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to reset password'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:avatar :name="$user->name" :initials="$user->initials()" />
                <div>
                    <flux:heading size="xl">{{ $user->name }}</flux:heading>
                    <flux:subheading>{{ $user->email }}</flux:subheading>
                </div>
            </div>
            <flux:button :href="route('admin.users.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Back') }}
            </flux:button>
        </div>

        <form wire:submit="save" class="grid w-full gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="name" :label="__('Name')" required />
                <flux:input wire:model="email" :label="__('Email')" type="email" required />
            </div>

            <div class="flex flex-col gap-3">
                <flux:checkbox
                    wire:model="is_admin"
                    :label="__('Grant admin access')"
                    :disabled="$user->id === auth()->id()"
                />
                @if ($user->id === auth()->id())
                    <flux:text size="sm" class="text-zinc-500">{{ __('You cannot change your own admin status.') }}</flux:text>
                @endif

                <flux:checkbox wire:model="verified" :label="__('Email is verified')" />
            </div>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
            </div>
        </form>

        <flux:separator />

        <div>
            <flux:heading size="lg">{{ __('Reset password') }}</flux:heading>
            <flux:subheading>{{ __('Set a new password for this user.') }}</flux:subheading>

            <form wire:submit="resetPassword" class="mt-4 grid w-full gap-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <flux:input
                        wire:model="password"
                        :label="__('New password')"
                        type="password"
                        autocomplete="new-password"
                        viewable
                    />
                    <flux:input
                        wire:model="password_confirmation"
                        :label="__('Confirm new password')"
                        type="password"
                        autocomplete="new-password"
                        viewable
                    />
                </div>

                <div>
                    <flux:button type="submit" variant="primary">{{ __('Reset password') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</section>
