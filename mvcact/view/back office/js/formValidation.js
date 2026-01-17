document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire d'activité
    const activityForm = document.querySelector('form[method="POST"]');
    if (activityForm) {
        activityForm.addEventListener('submit', function(event) {
            let isValid = true;
            let errorMessage = '';

            // Validation du nom
            const nameInput = document.getElementById('activityName');
            if (nameInput && nameInput.value.trim().length < 3) {
                isValid = false;
                errorMessage += 'Le nom de l\'activité doit contenir au moins 3 caractères.\n';
            }

            // Validation de la description
            const descriptionInput = document.getElementById('activityDescription');
            if (descriptionInput && descriptionInput.value.trim().length < 10) {
                isValid = false;
                errorMessage += 'La description doit contenir au moins 10 caractères.\n';
            }

            // Validation du prix
            const priceInput = document.getElementById('activityPrice');
            if (priceInput) {
                const price = parseFloat(priceInput.value);
                if (isNaN(price) || price < 0) {
                    isValid = false;
                    errorMessage += 'Le prix doit être un nombre positif.\n';
                }
            }

            // Validation du lieu
            const locationInput = document.getElementById('activityLocation');
            if (locationInput && locationInput.value.trim().length < 3) {
                isValid = false;
                errorMessage += 'Le lieu doit contenir au moins 3 caractères.\n';
            }

            // Validation de la date
            const dateInput = document.getElementById('activityDate');
            if (dateInput) {
                const selectedDate = new Date(dateInput.value);
                const currentDate = new Date();
                if (selectedDate < currentDate) {
                    isValid = false;
                    errorMessage += 'La date doit être dans le futur.\n';
                }
            }

            // Validation de la capacité
            const capacityInput = document.getElementById('activityCapacity');
            if (capacityInput) {
                const capacity = parseInt(capacityInput.value);
                if (isNaN(capacity) || capacity < 1) {
                    isValid = false;
                    errorMessage += 'La capacité doit être un nombre positif supérieur à 0.\n';
                }
            }

            // Validation de l'image (pour l'ajout uniquement)
            const imageInput = document.getElementById('imageFile');
            if (imageInput && imageInput.required && !imageInput.files.length) {
                isValid = false;
                errorMessage += 'Veuillez sélectionner une image pour l\'activité.\n';
            }

            if (!isValid) {
                event.preventDefault();
                alert(errorMessage);
            }
        });
    }

    // Validation en temps réel
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('error');
            const errorDiv = this.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('error-message')) {
                errorDiv.remove();
            }
        });
    });
}); 