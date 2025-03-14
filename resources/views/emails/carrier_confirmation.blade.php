<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Email</title>
</head>
<body>
    <h1>Hello {{ $userCarrier->name }}</h1>
    <p>Thank you for registering. Please confirm your email address by clicking the link below:</p>
    <a href="{{ $url }}">Confirm Email</a>
    <p>If you did not register, please ignore this email.</p>
</body>
</html>
