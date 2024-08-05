<?php

use App\Models\Country;
use App\Models\Language;
use App\Models\User;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithFileUploads;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email|unique:users,email')]
    public string $email = '';

    #[Rule('nullable|image|max:1024')]
    public $photo;

    #[Rule('sometimes')]
    public ?int $country_id = null;

    #[Rule('required')]
    public array $my_languages = [];

    #[Rule('required|min:8')]
    public string $password = '';

    public function with(): array
    {
        return [
            'countries' => Country::all(),
            'languages' => Language::all(),
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        // Hash the password before saving
        $data['password'] = bcrypt($this->password);

        $user = User::create($data);

        $user->languages()->sync($this->my_languages);

        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $user->update(['avatar' => "/storage/$url"]);
        }

        $this->success('User created with success.', redirectTo: '/users');
    }
}; ?>

<div>
    <x-header title="Create User" separator/>
    <div class="grid gap-5 lg:grid-cols-2">
        <div>
            <x-form wire:submit="save">
                <div class="lg:grid grid-cols-5">
                    <div class="col-span-2">
                        <x-header title="Basic" subtitle="Basic info from user" size="text-2xl"/>
                    </div>
                    <div class="col-span-3 grid gap-3">
                        <x-file label="Avatar" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                            <img src="/empty-user.jpg" class="h-40 rounded-lg" alt="File Avatar"/>
                        </x-file>

                        <x-input label="Name" wire:model="name"/>

                        <x-input label="Email" wire:model="email"/>

                        <x-input label="Password" wire:model="password" type="password"/>

                        <x-select label="Country" wire:model="country_id" :options="$countries" placeholder="---"/>
                    </div>
                </div>
                <hr class="my-5"/>

                <div class="lg:grid grid-cols-5">
                    <div class="col-span-2">
                        <x-header title="Details" subtitle="More about the user" size="text-2xl"/>
                    </div>
                    <div class="col-span-3 grid gap-3">
                        <x-choices-offline
                            label="My languages"
                            wire:model="my_languages"
                            :options="$languages"
                            searchable/>

                        <x-editor wire:model="bio" label="Bio" hint="The great biography"/>
                    </div>
                </div>
                <x-slot:actions>
                    <x-button label="Cancel" link="/users"/>
                    <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
                </x-slot:actions>
            </x-form>
        </div>
        <div>
            <img src="/edit-form.png" width="300" class="mx-auto" alt="A robot creating a User"/>
        </div>
    </div>
</div>
