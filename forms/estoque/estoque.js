const form = document.querySelector(".formulario");

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const dados = new FormData(e.target);

  await fetch("estoque.php", {
    method: "POST",
    body: dados,
  })
    .then((r) => r.json())
    .then((r) => {
      alert(r.status + " - " + r.mensagem);
      console.log(r.dados)
    })
    .catch((error) => {
      console.error("Erro: ", error);
    });
});
