
document.addEventListener('DOMContentLoaded', function() {

  window.toggleOptions = function(button) {
    const menu = button.nextElementSibling;

    // Fermer tous les autres menus
    document.querySelectorAll('.options-menu').forEach(m => {
      if (m !== menu) m.style.display = 'none';
    });

    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
  };

  document.addEventListener('click', function(e) {
    if (!e.target.closest('.chat-options')) {
      document.querySelectorAll('.options-menu').forEach(m => m.style.display = 'none');
    }
  });

  function activateClickOnMessages() {
    document.querySelectorAll('.chat-line').forEach(line => {
      line.addEventListener('click', function(e) {
        // 🛑 Ne pas afficher l'heure si click sur options
        if (e.target.closest('.chat-options') || e.target.classList.contains('options-btn') || e.target.closest('.options-menu')) {
          return;
        }

        const existing = this.querySelector('.chat-time');
        if (existing) {
          existing.remove();
        } else {
          const time = this.getAttribute('data-time');
          const timeSpan = document.createElement('span');
          timeSpan.className = 'chat-time';
          timeSpan.textContent = time;
          timeSpan.style.display = 'block';
          timeSpan.style.fontSize = '12px';
          timeSpan.style.color = '#999';
          timeSpan.style.marginTop = '5px';
          this.querySelector('.chat-message').appendChild(timeSpan);
        }
      });
    });
  }

  function loadMessages() {
    fetch('load_messages.php')
    .then(response => response.text())
    .then(html => {
      const messages = document.getElementById('messages');
      messages.innerHTML = html;
      messages.scrollTop = messages.scrollHeight;
      activateClickOnMessages();
    });
  }

  loadMessages();
  setInterval(loadMessages, 3000);

});


