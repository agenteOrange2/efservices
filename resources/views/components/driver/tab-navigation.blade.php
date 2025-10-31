@props(['tabs' => [], 'activeTab' => 'general'])

@php
    $defaultTabs = [
        'general' => ['label' => 'General Info', 'icon' => 'user'],
        'licenses' => ['label' => 'Licenses', 'icon' => 'credit-card'],
        'medical' => ['label' => 'Medical', 'icon' => 'heart-pulse'],
        'employment' => ['label' => 'Employment', 'icon' => 'briefcase'],
        'training' => ['label' => 'Training & Courses', 'icon' => 'graduation-cap'],
        'testing' => ['label' => 'Testing', 'icon' => 'flask'],
        'inspections' => ['label' => 'Inspections', 'icon' => 'search'],
        'documents' => ['label' => 'Documents', 'icon' => 'file-text']
    ];
    
    $tabsData = !empty($tabs) ? $tabs : $defaultTabs;
@endphp

<div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Mobile Tab Selector -->
        <div class="sm:hidden">
            <label for="tabs" class="sr-only">Select a tab</label>
            <select id="tabs" 
                    name="tabs" 
                    class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
                    onchange="switchTab(this.value)">
                @foreach($tabsData as $key => $tab)
                    <option value="{{ $key }}" {{ $activeTab === $key ? 'selected' : '' }}>
                        {{ $tab['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Desktop Tab Navigation -->
        <div class="hidden sm:block">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                    @foreach($tabsData as $key => $tab)
                        <button type="button"
                                onclick="switchTab('{{ $key }}')"
                                class="driver-tab whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === $key ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                data-tab="{{ $key }}"
                                aria-current="{{ $activeTab === $key ? 'page' : 'false' }}">
                            <div class="flex items-center space-x-2">
                                <x-base.lucide icon="{{ $tab['icon'] }}" class="w-4 h-4" />
                                <span>{{ $tab['label'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Show selected tab content
    const selectedContent = document.getElementById(tabName + '-tab');
    if (selectedContent) {
        selectedContent.classList.remove('hidden');
    }
    
    // Update tab navigation styles (desktop)
    document.querySelectorAll('.driver-tab').forEach(tab => {
        const tabKey = tab.getAttribute('data-tab');
        if (tabKey === tabName) {
            tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            tab.classList.add('border-blue-500', 'text-blue-600');
            tab.setAttribute('aria-current', 'page');
        } else {
            tab.classList.remove('border-blue-500', 'text-blue-600');
            tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            tab.setAttribute('aria-current', 'false');
        }
    });
    
    // Update mobile select
    const mobileSelect = document.getElementById('tabs');
    if (mobileSelect) {
        mobileSelect.value = tabName;
    }
    
    // Update URL hash without page reload
    if (history.pushState) {
        history.pushState(null, null, '#' + tabName);
    }
}

// Initialize tab from URL hash on page load
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.substring(1);
    if (hash && document.getElementById(hash + '-tab')) {
        switchTab(hash);
    }
});

// Handle browser back/forward buttons
window.addEventListener('popstate', function() {
    const hash = window.location.hash.substring(1);
    if (hash && document.getElementById(hash + '-tab')) {
        switchTab(hash);
    } else {
        switchTab('{{ $activeTab }}');
    }
});
</script>