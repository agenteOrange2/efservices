@component('mail::message')
# Hello {{ $name }}

Thank you for starting your driver registration process. Below are your login credentials to continue your registration process:

**Email:** {{ $email }}  
**Password:** {{ $password }}

@component('mail::button', ['url' => $resumeLink])
Continue Registration
@endcomponent

If you didn't complete your registration now, you can use these credentials to log in later and continue where you left off.

Thank you,<br>
{{ config('app.name') }}
@endcomponent