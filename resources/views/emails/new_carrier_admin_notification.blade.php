<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Carrier Admin Notification</title>
</head>
<body>
    @if($eventType === 'step_completed')
        <h1>Carrier Step Completed</h1>
        <p>A carrier has completed step: <strong>{{ $step }}</strong></p>
    @elseif($eventType === 'registration_completed')
        <h1>Carrier Registration Completed</h1>
        <p>A new carrier registration has been completed.</p>
    @else
        <h1>New Carrier Registered</h1>
        <p>A new carrier user has been registered:</p>
    @endif

    <ul>
        @if($userCarrier)
            {{-- Legacy system --}}
            <li><strong>Name:</strong> {{ $userCarrier->name }}</li>
            <li><strong>Email:</strong> {{ $userCarrier->email }}</li>
            <li><strong>Phone:</strong> {{ $userCarrier->phone }}</li>
            <li><strong>Position:</strong> {{ $userCarrier->job_position }}</li>
        @else
            {{-- New system --}}
            <li><strong>User Name:</strong> {{ $user->name ?? 'N/A' }}</li>
            <li><strong>User Email:</strong> {{ $user->email ?? 'N/A' }}</li>
            @if($carrier)
                <li><strong>Carrier Name:</strong> {{ $carrier->name ?? 'N/A' }}</li>
                <li><strong>Carrier Email:</strong> {{ $carrier->email ?? 'N/A' }}</li>
            @endif
            <li><strong>Event Type:</strong> {{ $eventType }}</li>
            @if($step)
                <li><strong>Step:</strong> {{ $step }}</li>
            @endif
        @endif
    </ul>

    @if(!empty($data))
        <h3>Additional Information:</h3>
        <ul>
            @foreach($data as $key => $value)
                <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</li>
            @endforeach
        </ul>
    @endif
</body>
</html>
