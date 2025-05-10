<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Carrier Admin Notification</title>
</head>
<body>
    <h1>New Carrier Registered</h1>
    <p>A new carrier user has been registered:</p>
    <ul>
        <li><strong>Name:</strong> {{ $userCarrier->name }}</li>
        <li><strong>Email:</strong> {{ $userCarrier->email }}</li>
        <li><strong>Phone:</strong> {{ $userCarrier->phone }}</li>
        <li><strong>Position:</strong> {{ $userCarrier->job_position }}</li>
    </ul>
</body>
</html>
