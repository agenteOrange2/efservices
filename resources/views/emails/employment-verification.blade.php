@component('mail::message')
# Employment Verification Request

Dear {{ $companyName }},

{{ $driverName }} has listed your company as a previous employer in their employment history. As part of our verification process, we kindly request your confirmation of the following employment details:

## Employment Details
- **Position Held**: {{ $employmentData['positions_held'] }}
- **Employment Period**: {{ $employmentData['employed_from'] }} to {{ $employmentData['employed_to'] }}
- **Reason for Leaving**: {{ $employmentData['reason_for_leaving'] }}

## Additional Information
@if(isset($employmentData['subject_to_fmcsr']) && $employmentData['subject_to_fmcsr'])
- The driver has indicated that they were subject to Federal Motor Carrier Safety Regulations (FMCSR) while employed at your company.
@endif

@if(isset($employmentData['safety_sensitive_function']) && $employmentData['safety_sensitive_function'])
- The driver has indicated that they performed safety-sensitive functions subject to drug and alcohol testing requirements while employed at your company.
@endif

Please click the button below to verify this employment information:

@component('mail::button', ['url' => url("/employment-verification/{$token}")])
Verify Employment Information
@endcomponent

This verification request will expire in 7 days. Your prompt response is greatly appreciated.

Thank you for your cooperation.

Sincerely,<br>
{{ config('app.name') }}

<small>If you are not the appropriate person to handle this request, please forward it to your HR department or the relevant personnel.</small>
@endcomponent
