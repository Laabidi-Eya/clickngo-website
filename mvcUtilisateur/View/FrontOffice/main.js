function scrollActivities(direction) {
    const container = document.querySelector('.activites');
    const scrollAmount = 220;
    if (direction === 'next') {
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    } else if (direction === 'prev') {
        container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    }
}

// Fonction pour afficher/masquer le menu déroulant
function toggleDropdown() {
    var dropdown = document.getElementById('dropdownMenu');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Ferme le menu déroulant si on clique en dehors
document.addEventListener('click', function(event) {
    var dropdown = document.getElementById('dropdownMenu');
    var profileCircle = document.querySelector('.profile-circle');
    if (profileCircle && !profileCircle.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

function toggleDropdown() {
    const menu = document.getElementById('dropdownMenu');
    menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
}

// Fermer le menu si on clique ailleurs
window.addEventListener('click', function(e) {
    const profile = document.querySelector('.user-profile');
    if (!profile.contains(e.target)) {
        document.getElementById('dropdownMenu').style.display = 'none';
    }
});