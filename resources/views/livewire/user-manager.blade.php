<div>
    <h2>Users for {{ optional($carrier)->name }}</h2>
    <button wire:click="createUser">Create User</button>

    @if ($isCreating)
        <!-- Formulario para Crear/Editar -->
        <div>
            <input type="text" wire:model="userCarrier.name" placeholder="Name">
            @error('userCarrier.name') <span class="text-red-500">{{ $message }}</span> @enderror

            <input type="email" wire:model="userCarrier.email" placeholder="Email">
            @error('userCarrier.email') <span class="text-red-500">{{ $message }}</span> @enderror

            <input type="text" wire:model="userCarrier.phone" placeholder="Phone">
            @error('userCarrier.phone') <span class="text-red-500">{{ $message }}</span> @enderror

            <select wire:model="userCarrier.status">
                <option value="1">Active</option>
                <option value="2">Inactive</option>
                <option value="3">Pending</option>
            </select>
            @error('userCarrier.status') <span class="text-red-500">{{ $message }}</span> @enderror

            <button wire:click="saveUser">Save</button>
            <button wire:click="$set('isCreating', false)">Cancel</button>
        </div>
    @else
        <!-- Tabla de Usuarios -->
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <button wire:click="editUser({{ $user->id }})">Edit</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
