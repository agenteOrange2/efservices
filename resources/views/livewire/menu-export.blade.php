<div>
    <x-base.menu>
        <x-base.menu.button class="w-full sm:w-auto" as="x-base.button" variant="outline-secondary">
            
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-icon lucide-download mr-2 h-4 w-4 stroke-[1.3]"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
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
