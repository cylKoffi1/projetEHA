<x-mail::message>
{{-- En-tête --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Oups !')
@else
# @lang('Bonjour !')
@endif
@endif

{{-- Introduction --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Bouton d'Action --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Lignes de Conclusion --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Cordialement'),<br>
**BTP-PROJECT - Gestion des Projets BTP**
@endif

{{-- Sous-Texte --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "Si vous rencontrez des difficultés pour cliquer sur le bouton \":actionText\", copiez et collez le lien ci-dessous\n".
    'dans votre navigateur web :',
    [
        'actionText' => $actionText,
    ]
)
<br>
<span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
