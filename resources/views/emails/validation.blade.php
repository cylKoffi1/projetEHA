@component('mail::message')
# Nouvelle validation de projet

Bonjour,

Le projet **{{ $libelleProjet }}** (`{{ $codeProjet }}`) a été **validé** par **{{ $approbateur }}**.

Vous êtes le prochain approbateur dans le circuit.  
Merci de vous connecter à l'application pour examiner et valider ce projet.

@component('mail::button', ['url' => route('projets.validation.index')])
Voir les projets à valider
@endcomponent

---

**Informations supplémentaires :**  
- **Projet :** {{ $libelleProjet }}
- **Code :** {{ $codeProjet }}
- **Dernière action :** Validation par {{ $approbateur }}  
- **Date :** {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}

Merci pour votre engagement.

Cordialement,  
L’équipe {{ config('app.name') }}
@endcomponent
