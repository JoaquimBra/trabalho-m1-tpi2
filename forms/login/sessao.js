const pastaLogin = document.currentScript.dataset.login || "./../login/";

fetch(pastaLogin + "login.php")
  .then((r) => r.json())
  .then((r) => {
    if (!r.autenticado) {
      window.location.href = pastaLogin + "login.html";
      return;
    }

    document.body.classList.remove("auth-carregando");

    const nav = document.querySelector(".navegacao");
    if (nav) {
      nav.classList.add("barra-usuario");

      const span = document.createElement("span");
      span.className = "usuario-logado";
      span.textContent = "Olá, " + r.nome;

      const btnSair = document.createElement("button");
      btnSair.type = "button";
      btnSair.className = "botao-sair";
      btnSair.textContent = "Sair";

      btnSair.addEventListener("click", () => {
        const dados = new FormData();
        dados.append("acao", "logout");

        fetch(pastaLogin + "login.php", { method: "POST", body: dados })
          .then(() => {
            window.location.href = pastaLogin + "login.html";
          });
      });

      nav.appendChild(span);
      nav.appendChild(btnSair);
    }
  });
