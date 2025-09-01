@extends('layouts.admin')

@section('title', 'Calendar')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Calendar</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.maintenance.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Add Maintenance
            </a>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">System Calendar</h6>
        </div>
        <div class="card-body">
            <!-- Calendar Container -->
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Include FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

<!-- Include FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: [
            // Sample events - replace with actual data from backend
            {
                title: 'Maintenance Due',
                start: '{{ date("Y-m-d") }}',
                backgroundColor: '#dc3545',
                borderColor: '#dc3545'
            }
        ],
        dateClick: function(info) {
            // Handle date click - redirect to create maintenance
            window.location.href = '{{ route("admin.maintenance.create") }}?date=' + info.dateStr;
        },
        eventClick: function(info) {
            // Handle event click
            alert('Event: ' + info.event.title);
        }
    });
    
    calendar.render();
});
</script>
@endsection