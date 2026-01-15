@component('mail::message')
@php
  $approverLabel = is_array($approbateur)
      ? trim(($approbateur['libelle_court'] ?? '').' '.($approbateur['libelle_long'] ?? ($approbateur['code'] ?? '')))
      : (string) $approbateur;
@endphp

# Refus de projet

Bonjour,

Le projet **{{ $libelleProjet }}** (`{{ $codeProjet }}`) a été **refusé** par **{{ $approverLabel ?: '—' }}**.
---

## 🛑 Motif du refus :
> {{ $commentaire }}

---

Nous vous invitons à consulter les détails du projet et à prendre les mesures nécessaires si besoin.

@component('mail::button', ['url' => route('approbations.dashboard')])
Voir les projets
@endcomponent

Merci pour votre compréhension.  
Cordialement,  
L’équipe {{ config('app.name') }}
@endcomponent
