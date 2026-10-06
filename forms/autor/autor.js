const form = document.querySelector("form");

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const dados = new FormData(e.target);

  fetch("autor.php", {
    method: "POST",
    body: dados,
  })
    .then((r) => r.json())
    .then((r) => {
      alert(r.status + " - " + r.mensagem);
    })
    .catch((err) => {
      console.error("Erro:", err);
    });
});
