@extends('../themes/' . $activeTheme)
@section('title', 'Dashboard Carrier EF Services ')

@section('subhead')
    <title>Carrier Dashboard - EF Services</title>
@endsection

@section('subcontent')
    <div class="py-5">
        <div class="mb-8">
            <h2 class="text-2xl font-medium">Welcome, {{ auth()->user()->name }}!</h2>
            <div class="mt-2 text-slate-500">
                Here's what's happening with your carrier account today.
            </div>
        </div>
        
        <!-- Dashboard Livewire Component -->
        @livewire('carrier.carrier-dashboard', ['carrier' => $carrier])
    </div>
@endsection