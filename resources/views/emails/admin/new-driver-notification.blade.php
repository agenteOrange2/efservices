@component('mail::message')
# New Driver Registration

A new driver has been registered in the system:

**Driver Name:** {{ $driverName }}  
**Driver Email:** {{ $driverEmail }}  
**Carrier:** {{ $carrierName }}

@component('mail::button', ['url' => $driverLink])
View Drivers
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent
