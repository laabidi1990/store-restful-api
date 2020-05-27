
@component('mail::message')
Hello {{ $user->name }}

The email of this account has been changed, please verify your new email by clicking the button below:

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Verify Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent