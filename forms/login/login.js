fetch("login.php")
  .then((r) => r.json())
  .then((r) => {
    if (r.autenticado) {
      window.location.href = "./../../index.html";
    }
  });

const formLogin = document.querySelector("#form-login");
if (formLogin) {
  formLogin.addEventListener("submit", (e) => {
    e.preventDefault();

    fetch("login.php", {
      method: "POST",
      body: new FormData(e.target),
    })
      .then((r) => r.json())
      .then((r) => {
        if (r.status === "Sucesso") {
          window.location.href = "./../../index.html";
        } else {
          alert(r.status + " - " + r.mensagem);
        }
      });
  });
}

const formCadastro = document.querySelector("#form-cadastro");
if (formCadastro) {
  formCadastro.addEventListener("submit", (e) => {
    e.preventDefault();

    fetch("login.php", {
      method: "POST",
      body: new FormData(e.target),
    })
      .then((r) => r.json())
      .then((r) => {
        alert(r.status + " - " + r.mensagem);
        if (r.status === "Sucesso") {
          formCadastro.reset();
          window.location.href = "./login.html";
        }
      });
  });
}
