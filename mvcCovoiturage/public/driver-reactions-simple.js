document.addEventListener("DOMContentLoaded", () => {
  // Handle Like/Dislike button clicks
  document.querySelectorAll(".reaction-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const driverId = this.dataset.driverId
      const reaction = this.dataset.reaction
      const container = this.closest(".top-driver")
      const likeBtn = container.querySelector(".like-btn")
      const dislikeBtn = container.querySelector(".dislike-btn")
      const likeCount = container.querySelector(".like-count")
      const dislikeCount = container.querySelector(".dislike-count")

      // Show loading state
      this.classList.add("loading")

      fetch("/clickngo/handle_reaction_simple.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ driver_id: driverId, reaction: reaction }),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network response was not ok: " + response.status)
          }
          return response.json()
        })
        .then((data) => {
          // Remove loading state
          this.classList.remove("loading")

          if (data.success) {
            // Update counts
            likeCount.textContent = data.likes
            dislikeCount.textContent = data.dislikes

            // Update active states
            likeBtn.classList.toggle("active", data.user_reaction === "like")
            dislikeBtn.classList.toggle("active", data.user_reaction === "dislike")
          } else {
            console.error("Error:", data.error)
            alert("Erreur: " + data.error)
          }
        })
        .catch((error) => {
          // Remove loading state
          this.classList.remove("loading")

          console.error("Fetch error:", error)
          alert("Erreur réseau. Veuillez réessayer. Détails: " + error.message)
        })
    })
  })
})
