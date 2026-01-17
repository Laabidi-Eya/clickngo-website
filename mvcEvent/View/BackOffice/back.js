
const eventsTableBody = document.querySelector('#events-table tbody');
const searchInput = document.getElementById('search-input');
const addEventBtn = document.getElementById('add-event-btn');
const eventFormPanel = document.getElementById('event-form-panel');
const closePanel = document.getElementById('close-panel');
const eventForm = document.getElementById('event-form');
const panelTitle = document.getElementById('panel-title');
const eventIdGroup = document.getElementById('event-id-group');


addEventBtn.addEventListener('click', () => {
    panelTitle.textContent = "Ajouter un Événement";
    eventForm.reset();
    eventIdGroup.style.display = 'none';
    eventFormPanel.classList.add('open');
});

closePanel.addEventListener('click', () => {
    eventFormPanel.classList.remove('open');
});




document.addEventListener("DOMContentLoaded", function() {
    const eventForm = document.getElementById("event-form");
    const closePanel = document.getElementById("close-panel");

    // Fermer le panneau
    closePanel.addEventListener("click", function() {
        document.getElementById("event-form-panel").classList.remove("open");
    });

    // Validation en temps réel
    document.getElementById("event-name").addEventListener("input", validateEventName);
    document.getElementById("event-category");
    document.getElementById("event-description").addEventListener("input", validateEventDescription);
    document.getElementById("event-price").addEventListener("input", validateEventPrice);
    document.getElementById("event-duration").addEventListener("input", validateEventDuration);
    document.getElementById("event-date").addEventListener("change", validateEventDate);
    document.getElementById("event-location").addEventListener("input", validateEventLocation);
    document.getElementById("event-image").addEventListener("input", validateEventImage);
    document.getElementById("event-total-seats").addEventListener("input", validateEventTotalSeats);
    document.getElementById("event-rating").addEventListener("input", validateEventRating);

    // Fonctions de validation
    function validateEventName() {
        const name = document.getElementById("event-name").value.trim();
        const errorElement = document.getElementById("event-nameError");

        if (!/^[a-zA-ZÀ-ÿ\s]{3,}$/.test(name)) {
            errorElement.textContent = "Le nom doit contenir uniquement des lettres et espaces (min 3 caractères).";
            errorElement.classList.add("error");
            errorElement.classList.remove("valid");
            return false;
        } else {
            errorElement.textContent = "✔ Correct";
            errorElement.classList.add("valid");
            errorElement.classList.remove("error");
            return true;
        }
    }

    function validateEventDescription() {
        const description = document.getElementById("event-description").value.trim();
        const errorElement = document.getElementById("event-descriptionError");
        const wordCount = description ? description.split(/\s+/).length : 0;

        if (wordCount > 300) {
            errorElement.textContent = `La description ne doit pas dépasser 300 mots (${wordCount}/300).`;
            errorElement.classList.add("error");
            errorElement.classList.remove("valid");
            return false;
        } else {
            errorElement.textContent = wordCount > 0 ? `✔ Correct (${wordCount}/300 mots)` : "";
            errorElement.classList.add("valid");
            errorElement.classList.remove("error");
            return true;
        }
    }

    function validateEventLocation() {
        const location = document.getElementById("event-location").value.trim();
        const errorElement = document.getElementById("event-locationError");

        if (!/^[a-zA-ZÀ-ÿ\s]{3,}$/.test(location)) {
            errorElement.textContent = "Le lieu doit contenir uniquement des lettres et espaces (min 3 caractères).";
            errorElement.classList.add("error");
            errorElement.classList.remove("valid");
            return false;
        } else {
            errorElement.textContent = "✔ Correct";
            errorElement.classList.add("valid");
            errorElement.classList.remove("error");
            return true;
        }
    }

    function validateEventDuration() {
        const duration = document.getElementById("event-duration").value.trim();
        const errorElement = document.getElementById("event-durationError");

        if (!/^\d{1,2}h$/.test(duration) || parseInt(duration) > 8) {
            errorElement.textContent = "La durée doit être au format 'Xh' et ne pas dépasser 8h (ex: 4h).";
            errorElement.classList.add("error");
            errorElement.classList.remove("valid");
            return false;
        } else {
            errorElement.textContent = "✔ Correct";
            errorElement.classList.add("valid");
            errorElement.classList.remove("error");
            return true;
        }
    }

    function validateEventImage() {
        const image = document.getElementById("event-image").value.trim();
        const errorElement = document.getElementById("event-imageError");
    
        if (image) {
            if (image.length < 3) {
                errorElement.textContent = "Le chemin de l'image doit contenir au moins 3 caractères.";
                errorElement.classList.add("error");
                errorElement.classList.remove("valid");
                return false;
            } else if (!image.includes('/')) {
                errorElement.textContent = "Le chemin de l'image doit contenir au moins un '/'.";
                errorElement.classList.add("error");
                errorElement.classList.remove("valid");
                return false;
            } else {
                errorElement.textContent = "✔ Correct";
                errorElement.classList.add("valid");
                errorElement.classList.remove("error");
                return true;
            }
        } else {
            errorElement.textContent = "";
            errorElement.classList.remove("error", "valid");
            return true; // Le champ est optionnel
        }
    }

    // Les autres fonctions de validation restent identiques à précédemment
   

    function validateEventPrice() {
        const price = document.getElementById("event-price").value;
        const errorElement = document.getElementById("event-priceError");

        if (!/^\d+(\.\d{1,2})?$/.test(price) || parseFloat(price) <= 0) {
            errorElement.textContent = "Veuillez entrer un prix valide (nombre positif).";
            errorElement.classList.add("error");
            errorElement.classList.remove("valid");
            return false;
        } else {
            errorElement.textContent = "✔ Correct";
            errorElement.classList.add("valid");
            errorElement.classList.remove("error");
            return true;
        }
    }

    function validateEventDate() {
        const date = document.getElementById("event-date").value;
        const errorElement = document.getElementById("event-dateError");
        const now = new Date();
        const selectedDate = new Date(date);

        if (!date) {
            errorElement.textContent = "Veuillez sélectionner une date.";
            errorElement.classList.add("error");
            errorElement.classList.remove("valid");
            return false;
        } else if (selectedDate < now) {
            errorElement.textContent = "La date doit être dans le futur.";
            errorElement.classList.add("error");
            errorElement.classList.remove("valid");
            return false;
        } else {
            errorElement.textContent = "✔ Correct";
            errorElement.classList.add("valid");
            errorElement.classList.remove("error");
            return true;
        }
    }

    function validateEventTotalSeats() {
        const seats = document.getElementById("event-total-seats").value;
        const errorElement = document.getElementById("event-total-seatsError");

        if (!seats || isNaN(seats) || parseInt(seats) <= 0) {
            errorElement.textContent = "Veuillez entrer un nombre de sièges valide (au moins 1).";
            errorElement.classList.add("error");
            errorElement.classList.remove("valid");
            return false;
        } else {
            errorElement.textContent = "✔ Correct";
            errorElement.classList.add("valid");
            errorElement.classList.remove("error");
            return true;
        }
    }

    function validateEventRating() {
        const rating = document.getElementById("event-rating").value;
        const errorElement = document.getElementById("event-ratingError");

        if (rating && (isNaN(rating) || parseFloat(rating) < 0 || parseFloat(rating) > 5)) {
            errorElement.textContent = "La note doit être entre 0 et 5.";
            errorElement.classList.add("error");
            errorElement.classList.remove("valid");
            return false;
        } else if (rating) {
            errorElement.textContent = "✔ Correct";
            errorElement.classList.add("valid");
            errorElement.classList.remove("error");
            return true;
        } else {
            errorElement.textContent = "";
            errorElement.classList.remove("error", "valid");
            return true;
        }
    }

    // Soumission du formulaire
    eventForm.addEventListener("submit", function(e) {
        e.preventDefault();
        let isValid = true;

        // Valider tous les champs
        if (!validateEventName()) isValid = false;
        if (!validateEventDescription()) isValid = false;
        if (!validateEventPrice()) isValid = false;
        if (!validateEventDuration()) isValid = false;
        if (!validateEventDate()) isValid = false;
        if (!validateEventLocation()) isValid = false;
        if (!validateEventImage()) isValid = false;
        if (!validateEventTotalSeats()) isValid = false;
        if (!validateEventRating()) isValid = false;

        if (isValid) {
            console.log("Formulaire d'événement valide, prêt à être soumis !");
            // this.submit(); // Décommentez pour la soumission réelle
            
            // Animation de succès
            const submitBtn = document.getElementById("submitBtn");
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Enregistré !';
            submitBtn.style.backgroundColor = '#2ecc71';
            
            setTimeout(() => {
                document.getElementById("event-form-panel").classList.remove("open");
                submitBtn.innerHTML = 'Enregistrer';
                submitBtn.style.backgroundColor = '';
                eventForm.reset();
            }, 1500);
        }
    });
});

function toggleDropdown() {
    const menu = document.getElementById('dropdownMenu');
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}

document.addEventListener('click', function(event) {
    const profile = document.querySelector('.user-profile');
    const dropdown = document.getElementById('dropdownMenu');
    if (profile && dropdown && !profile.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});