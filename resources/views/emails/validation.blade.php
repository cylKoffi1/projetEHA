@component('mail::message')
# Nouvelle validation de projet

Bonjour @if(!empty($destinataire['libelle_court']) || !empty($destinataire['libelle_long']))
{{ $destinataire['libelle_court'] ?? $destinataire['libelle_long'] }}
@else
,
@endif

Le projet **{{ $libelleProjet }}** (`{{ $codeProjet }}`) a été **validé** par **{{ $lastActorLabel }}**.

Vous êtes le prochain approbateur dans le circuit.  
Merci de vous connecter à l'application pour examiner et valider ce projet.

@component('mail::button', ['url' => $ctaUrl ?? route('approbations.dashboard')])
Voir les projets à valider
@endcomponent

---

**Informations supplémentaires :**  
- **Projet :** {{ $libelleProjet }}  
- **Code :** {{ $codeProjet }}  
- **Dernière action :** Validation par {{ $lastActorLabel }}  
- **Date :** {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}

Cordialement,  
L’équipe {{ config('app.name') }}
@endcomponent
