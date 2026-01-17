async function getBotResponse(question) {
  const response = await fetch('https://librechat.herokuapp.com/api/ask', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      question: question + " (réponds uniquement sur le covoiturage)",
      context: "Tu es un assistant spécialisé dans le covoiturage en France."
    })
  });
  return await response.json();
}