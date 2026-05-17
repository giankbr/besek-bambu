<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('New user')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_admin = false;
    public bool $verified = true;

    public function save(): void
    {
        try {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => ['required', 'string', 'confirmed', Password::default()],
                'is_admin' => ['boolean'],
                'verified' => ['boolean'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            // is_admin / email_verified_at are not mass assignable.
            $user->is_admin = (bool) $validated['is_admin'];
            $user->email_verified_at = $this->verified ? now() : null;
            $user->save();

            Flux::toast(variant: 'success', text: __('User created.'));
            $this->redirectRoute('admin.users.index', navigate: true);
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to create'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form for errors.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to create'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('New user') }}</flux:heading>
                <flux:subheading>{{ __('Create a customer or admin account.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.users.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Back') }}
            </flux:button>
        </div>

        <form wire:submit="save" class="grid w-full gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="name" :label="__('Name')" required autocomplete="name" />
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <flux:input
                    wire:model="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    viewable
                />
                <flux:input
                    wire:model="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    viewable
                />
            </div>

            <div class="flex flex-col gap-3">
                <flux:checkbox wire:model="is_admin" :label="__('Grant admin access')" />
                <flux:checkbox wire:model="verified" :label="__('Mark email as verified')" />
            </div>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Create user') }}</flux:button>
                <flux:button :href="route('admin.users.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</section>
