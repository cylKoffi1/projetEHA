@component('mail::message')
# Refus de projet

Bonjour,

Le projet **{{ $libelleProjet }}** (`{{ $codeProjet }}`) a été **refusé** par **{{ $approbateur }}**.

---

## 🛑 Motif du refus :
> {{ $commentaire }}

---

Nous vous invitons à consulter les détails du projet et à prendre les mesures nécessaires si besoin.

@component('mail::button', ['url' => route('projets.validation.index')])
Voir les projets
@endcomponent

Merci pour votre compréhension.  
Cordialement,  
L’équipe {{ config('app.name') }}
@endcomponent
