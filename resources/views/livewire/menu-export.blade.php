<div>
    <x-base.menu>
        <x-base.menu.button class="w-full sm:w-auto" as="x-base.button" variant="outline-secondary">
            <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Download" />
            Export
            <x-base.lucide class="ml-2 h-4 w-4 stroke-[1.3]" icon="ChevronDown" />
        </x-base.menu.button>
        <x-base.menu.items class="w-40">
            @if ($exportExcel)
                <x-base.menu.item x-on:click="$dispatch('exportToExcel')">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="FileBarChart" />
                    CSV
                </x-base.menu.item>
            @endif
            @if ($exportPdf)
                <x-base.menu.item x-on:click="$dispatch('exportToPdf')">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="FileBarChart" />
                    PDF
                </x-base.menu.item>
            @endif
        </x-base.menu.items>
    </x-base.menu>
</div>
