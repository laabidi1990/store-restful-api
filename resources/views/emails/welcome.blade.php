Hello {{ $user->name }}
Your account has been created successfully, please verify your email by clicking the button below:
{{ route('verify', $user->verification_token) }}


@component('mail::message')
Hello {{ $user->name }}

Your account has been created successfully, please verify your email by clicking the button below:

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Verify Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent