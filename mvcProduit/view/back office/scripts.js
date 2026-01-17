// Chart.js Configurations for Dashboard
function initializeCharts() {
    // Graphique en anneau pour la répartition du stock
    const stockDonut = new Chart(document.getElementById('stockDonut'), {
      type: 'doughnut',
      data: {
        labels: ['Sport', 'Tech', 'Bien-être', 'Vêtements'],
        datasets: [{
          data: [150, 60, 45, 90],
          backgroundColor: ['#ff6b6b', '#4b6cb7', '#ff8fa3', '#82cffa'],
          borderWidth: 0,
          hoverOffset: 30
        }]
      },
      options: {
        responsive: true,
        animation: {
          animateScale: true,
          animateRotate: true
        },
        plugins: {
          legend: {
            position: 'bottom',
            labels: { font: { size: 12, weight: '600' }, color: '#333', padding: 20 }
          }
        },
        cutout: '70%'
      }
    });
  
    const salesWave = new Chart(document.getElementById('salesWave'), {
      type: 'line',
      data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr'],
        datasets: [{
          label: 'Ventes (TND)',
          data: [5000, 7000, 4000, 9000],
          borderColor: '#ff6b6b',
          backgroundColor: 'rgba(255, 107, 107, 0.2)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#ff6b6b',
          pointBorderWidth: 2
        }]
      },
      options: {
        responsive: true,
        animation: {
          duration: 2000,
          easing: 'easeOutQuart'
        },
        plugins: {
          legend: { display: false },
          title: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: '#333', font: { size: 12 } },
            grid: { color: 'rgba(0, 0, 0, 0.05)' }
          },
          x: {
            ticks: { color: '#333', font: { size: 12 } },
            grid: { display: false }
          }
        }
      }
    });
  
    // Graphique des ventes par catégorie
    const categorySalesBar = new Chart(document.getElementById('categorySalesBar'), {
      type: 'bar',
      data: {
        labels: ['Sport', 'Tech', 'Vêtements', 'Bien-être'],
        datasets: [{
          label: 'Ventes (TND)',
          data: [5000, 3000, 2000, 1500],
          backgroundColor: ['#FF69B4', '#9370DB', '#ff8fa3', '#82cffa'],
          borderWidth: 0,
          borderRadius: 10,
          barThickness: 30
        }]
      },
      options: {
        responsive: true,
        animation: {
          duration: 2000,
          easing: 'easeOutQuart'
        },
        plugins: {
          legend: { display: false },
          title: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: '#333', font: { size: 12 } },
            grid: { color: 'rgba(0, 0, 0, 0.05)' }
          },
          x: {
            ticks: { color: '#333', font: { size: 12 } },
            grid: { display: false }
          }
        }
      }
    });

    // Graphique des statistiques par catégorie
    new Chart(document.getElementById('categoryStats'), {
      type: 'radar',
      data: {
        labels: [
          'Équipements Sportifs',
          'Vêtements et Accessoires',
          'Gadgets & Technologies',
          'Articles de Bien-être',
          'Nutrition & Hydratation',
          'Accessoires de Voyage',
          'Supports d\'atelier',
          'Univers du cerveau'
        ],
        datasets: [{
          label: 'Nombre de produits',
          data: [42, 35, 28, 25, 20, 18, 15, 12],
          backgroundColor: 'rgba(147, 112, 219, 0.4)',
          borderColor: '#9370DB',
          pointBackgroundColor: '#FF69B4',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: '#FF69B4',
          borderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        elements: {
          line: {
            tension: 0.4
          }
        },
        plugins: {
          legend: {
            position: 'top',
            labels: {
              font: {
                size: 14,
                family: 'Inter'
              },
              color: '#333'
            }
          }
        },
        scales: {
          r: {
            angleLines: {
              color: 'rgba(147, 112, 219, 0.2)'
            },
            grid: {
              color: 'rgba(147, 112, 219, 0.1)'
            },
            pointLabels: {
              font: {
                size: 12,
                weight: '600',
                family: 'Inter'
              },
              color: '#333'
            },
            ticks: {
              backdropColor: 'transparent',
              color: '#666'
            }
          }
        },
        animation: {
          duration: 2000,
          easing: 'easeOutQuart'
        }
      }
    });
}
  
  function initializeNavigation() {
    const menuItems = document.querySelectorAll('.menu-item');
    const sections = document.querySelectorAll('.dashboard-section');
  
    menuItems.forEach(item => {
      item.addEventListener('click', () => {
        menuItems.forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        const sectionId = item.getAttribute('data-section');
        sections.forEach(section => section.classList.remove('active'));
        document.getElementById(sectionId).classList.add('active');
      });
    });
  }
  
  function openPromoModal() {
    const modal = document.getElementById('promoModal');
    modal.style.display = 'flex';
  }
  
  function closeModal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => modal.style.display = 'none');
  }
  
  function updateOrderStatus(id, status) {
    alert(`Commande ${id} marquée comme ${status}`);
  }
  
  function editPromo(button) {
    alert('Modifier la promotion');
  }
  
  function deletePromo(button) {
    if (confirm('Supprimer cette promotion ?')) {
      alert('Promotion supprimée');
    }
  }
  
  function approveReview(button) {
    alert('Avis approuvé');
  }
  
  function rejectReview(button) {
    alert('Avis rejeté');
  }
  
  function initializeSearch() {
    document.querySelectorAll('.search').forEach(searchInput => {
      searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const section = e.target.closest('.dashboard-section');
        if (section.id === 'products') {
          const cards = section.querySelectorAll('.card');
          cards.forEach(card => {
            const name = card.querySelector('h3').textContent.toLowerCase();
            card.style.display = name.includes(query) ? 'block' : 'none';
          });
        } else if (section.id === 'orders') {
          // Add search for orders
        }
      });
    });
  }
  
  
  
 
  // Function to open product modal for add/edit
  function openProductModal(mode, productId = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const modalTitle = document.getElementById('modalTitle');
    
    // Reset form
    form.reset();
    
    if (mode === 'add') {
      modalTitle.textContent = 'Ajouter un Produit';
      form.action = '../../Controller/produitcontroller.php';
      form.method = 'POST';
      
      // Add action input
      const actionInput = document.createElement('input');
      actionInput.type = 'hidden';
      actionInput.name = 'action';
      actionInput.value = 'add';
      form.appendChild(actionInput);
      
    } else if (mode === 'edit' && productId) {
      modalTitle.textContent = 'Modifier un Produit';
      form.action = '../../Controller/produitcontroller.php';
      form.method = 'POST';
      
      // Add action and id inputs
      const actionInput = document.createElement('input');
      actionInput.type = 'hidden';
      actionInput.name = 'action';
      actionInput.value = 'update';
      form.appendChild(actionInput);
      
      const idInput = document.createElement('input');
      idInput.type = 'hidden';
      idInput.name = 'id';
      idInput.value = productId;
      form.appendChild(idInput);
      
      // Fetch product details and fill the form
      fetch(`../../Controller/produitcontroller.php?action=get_one&id=${productId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const product = data.product;
            document.getElementById('productName').value = product.name;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productCategory').value = product.category;
            document.getElementById('productPurchase').value = product.purchase_available === 1 ? 'yes' : 'no';
            document.getElementById('productRental').value = product.rental_available === 1 ? 'yes' : 'no';
          } else {
            alert('Erreur lors du chargement des détails du produit: ' + data.error);
            closeModal();
          }
        })
        .catch(error => {
          console.error('Error fetching product details:', error);
          alert('Erreur lors du chargement des détails du produit');
          closeModal();
        });
    }
    
    modal.style.display = 'flex';
  }
  
  // Function to confirm and handle product deletion
  function confirmDelete(productId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce produit?')) {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id', productId);
      
      fetch('../../Controller/produitcontroller.php', {
        method: 'POST',
        body: formData
      })
      .then(response => {
        // Reload products after deletion
        
        alert('Produit supprimé avec succès');
      })
      .catch(error => {
        console.error('Error deleting product:', error);
        alert('Erreur lors de la suppression du produit');
      });
    }
  }
  
  document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('stockDonut')) {
      initializeCharts();
    }
    initializeNavigation();
    initializeSearch();
    
   
  });


// afficher un aperçu de l'image avant upload
  document.getElementById('productPhoto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('previewImage').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});

function handleFormSubmit(formId, action) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('action', action);

        fetch('../Controller/produitcontroller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Rediriger vers la section des produits
            window.location.href = 'indeex.php#products';
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
}

// Initialisation du graphique des commandes
function initOrdersChart() {
    const ctx = document.getElementById('ordersStats');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Commandes d\'achat', 'Locations'],
            datasets: [{
                label: 'Nombre de commandes',
                data: [
                    parseInt(document.querySelector('.stat-details').textContent.match(/Achats: (\d+)/)[1]),
                    parseInt(document.querySelector('.stat-details').textContent.match(/Locations: (\d+)/)[1])
                ],
                backgroundColor: [
                    'rgba(216, 27, 96, 0.7)',  // Rose pour les achats
                    'rgba(2, 136, 209, 0.7)'   // Bleu pour les locations
                ],
                borderColor: [
                    'rgb(216, 27, 96)',
                    'rgb(2, 136, 209)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Répartition des Commandes',
                    font: {
                        family: 'Inter',
                        size: 16,
                        weight: 'bold'
                    }
                }
            }
        }
    });
}

// Appeler l'initialisation du graphique des commandes
document.addEventListener('DOMContentLoaded', function() {
    // ... existing code ...
    initOrdersChart();
});