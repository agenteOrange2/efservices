@extends('../themes/' . $activeTheme)

@section('title', 'Super Admins')

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Users
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.users.create') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New User
                    </x-base.button>
                </div>
            </div>
            <div class="box box--stacked flex flex-col">
                <livewire:generic-table model="App\Models\User" :columns="['name', 'email', 'created_at', 'updated_at']" :searchableFields="['name', 'email','created_at']" />
            </div>

        </div>
    </div>

    
@endsection


