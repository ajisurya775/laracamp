@component('mail::message')
# Registrasi Camp: {{$checkout->camp->title}}

Hi! {{$checkout->user->name}}
<br>
Thank you for register on <b>{{ $checkout->camp->title}}</b>, Please see payment instruction by click the button below.

@component('mail::button', ['url' => route('user.checkout.invoice', $checkout->id)])
Get Invoice
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
