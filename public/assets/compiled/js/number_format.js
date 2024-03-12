// script.js

// Fonction de formatage du nombre avec espaces comme séparateurs de milliers
function number_format(number, decimals, decPoint, thousandsSep) {
    number = parseFloat(number);
    decimals = decimals || 0;
    var fixed = number.toFixed(decimals);
    var parts = fixed.split('.');
    var intPart = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1" + thousandsSep);
    var decPart = parts.length > 1 ? (decPoint + parts[1]) : '';
    return intPart + decPart;
}

// Fonction de formatage générique pour les champs de nombre
function formatNumberInput(input) {
    // Supprimer tout sauf les chiffres et le séparateur décimal
    var sanitizedValue = input.value.replace(/[^0-9.]/g, '');

    // Séparer la partie entière et la partie décimale
    var parts = sanitizedValue.split(' ');
    var integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

    // Recréer la valeur avec le séparateur de milliers
    var formattedValue = integerPart;
    if (parts.length > 1) {
        formattedValue += ' ' + parts[1];
    }

    // Mettre à jour la valeur du champ
    input.value = formattedValue;
}

// Gérer l'événement de saisie pour les champs de nombre avec la classe "number-input"
document.addEventListener('input', function (event) {
    if (event.target.classList.contains('number-input')) {
        formatNumberInput(event.target);
    }
});
