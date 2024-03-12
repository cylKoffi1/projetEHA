// Sélectionnez les éléments du formulaire
const dateDebutInput = document.getElementById('date_debut');
const dateFinInput = document.getElementById('date_fin');

// Ajoutez un auditeur d'événements 'change' aux champs de date
dateDebutInput.addEventListener('change', handleDateChange);
dateFinInput.addEventListener('change', handleDateChange);

// Fonction pour gérer les changements de date
function handleDateChange() {
    // Obtenez les valeurs des dates
    const dateDebutValue = dateDebutInput.value;
    const dateFinValue = dateFinInput.value;

    // Convertissez les valeurs des dates en objets de date JavaScript
    const dateDebut = new Date(dateDebutValue);
    const dateFin = new Date(dateFinValue);

    // Vérifiez si la date de début est strictement inférieure à la date de fin
    if (dateDebut > dateFin) {
        alert("La date de démarrage doit être strictement inférieure à la date de fin.");
        // Réinitialisez la valeur du champ de date de fin
        dateFinInput.value = '';
    }
}


// Récupérer l'élément input
var quantiteInput = document.getElementById('quantite');
var montantInput = document.getElementById('montant');

// Fonction pour gérer les champs de quantité et de montant
function handleInput(inputElement) {
    // Convertir la valeur en nombre
    var inputValue = parseInt(inputElement.value, 10);

    // Vérifier si la valeur est négative
    if (inputValue < 0) {
        // Si c'est le cas, réinitialiser la valeur à zéro
        inputElement.value = 0;
    }
}

// Ajouter des écouteurs d'événements pour l'événement input
quantiteInput.addEventListener('input', function() {
    handleInput(quantiteInput);
});

montantInput.addEventListener('input', function() {
    handleInput(montantInput);
});

const navigateToFormStep = (stepNumber) => {
    document.querySelectorAll(".form-step").forEach((formStepElement) => {
        formStepElement.classList.add("d-none");
    });

    document.querySelectorAll(".form-stepper-list").forEach((formStepHeader) => {
        formStepHeader.classList.add("form-stepper-unfinished");
        formStepHeader.classList.remove("form-stepper-active", "form-stepper-completed");
    });

    document.querySelector("#step-" + stepNumber).classList.remove("d-none");

    const formStepCircle = document.querySelector('li[step="' + stepNumber + '"]');

    formStepCircle.classList.remove("form-stepper-unfinished", "form-stepper-completed");
    formStepCircle.classList.add("form-stepper-active");

    for (let index = 0; index < stepNumber; index++) {
        const formStepCircle = document.querySelector('li[step="' + index + '"]');
        if (formStepCircle) {
            formStepCircle.classList.remove("form-stepper-unfinished", "form-stepper-active");
            formStepCircle.classList.add("form-stepper-completed");
        }
    }
};

document.querySelectorAll(".btn-navigate-form-step").forEach((formNavigationBtn) => {
    formNavigationBtn.addEventListener("click", () => {
        const stepNumber = parseInt(formNavigationBtn.getAttribute("step_number"));
        navigateToFormStep(stepNumber);
    });
});
