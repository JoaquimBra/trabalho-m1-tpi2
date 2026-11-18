const modal = document.querySelector("#modal");
const form = document.querySelector("#cadastro");
const pesquisar = document.querySelector("#btn-pesquisar");

document.querySelector(".botao-abrir-modal").addEventListener("click", () => {
  modal.showModal();
});

document.querySelector(".botao-fechar-modal").addEventListener("click", () => {
  modal.close();
});

// Fecha ao clicar fora da caixa (no backdrop)
modal.addEventListener("click", (e) => {
  const area = modal.getBoundingClientRect();
  const dentro =
    e.clientX >= area.left &&
    e.clientX <= area.right &&
    e.clientY >= area.top &&
    e.clientY <= area.bottom;
  if (!dentro) modal.close();
});

// Abrir
document.querySelectorAll("[data-abrir]").forEach((botao) => {
  botao.addEventListener("click", () => {
    document.getElementById(botao.dataset.abrir).showModal();
  });
});

// Fechar pelo botão × e ao clicar fora da caixa
document.querySelectorAll("dialog.modal").forEach((modal) => {
  modal
    .querySelector(".botao-fechar-modal")
    .addEventListener("click", () => modal.close());

  modal.addEventListener("click", (e) => {
    const area = modal.getBoundingClientRect();
    const dentro =
      e.clientX >= area.left &&
      e.clientX <= area.right &&
      e.clientY >= area.top &&
      e.clientY <= area.bottom;
    if (!dentro) modal.close();
  });
});

// Mostra/esconde a mensagem de tabela vazia
const corpoTabela = document.getElementById("corpoTabela");
const msgVazia = document.getElementById("tabela-vazia");

function atualizarVazio() {
  msgVazia.hidden = corpoTabela.rows.length > 0;
}

new MutationObserver(atualizarVazio).observe(corpoTabela, { childList: true });
atualizarVazio();

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const dados = new FormData(e.target);

  await fetch("editora.php", {
    method: "POST",
    body: dados,
  })
    .then((r) => r.json())
    .then((r) => {
      form.reset();
      alert(r.status + " - " + r.mensagem);
      console.log(r.dados);
    })
    .catch((error) => {
      console.error("Erro: ", error);
    });
});

pesquisar.addEventListener("click", () => {
  fetch("editora.php", {
    method: "GET"
  })
    .then((r) => r.json())
    .then((r) => {
      if (corpoTabela.children.length > 0) {
        corpoTabela.replaceChildren();
      }
      r.dados.forEach((valores) => {
        adicionarLinhaTabela(corpoTabela, valores);
      });
    })
    .catch((error) => {
      console.error("Erro: ", error);
    });
});

function editar(dados) {
  fetch("editora.php", {
    method: "UPDATE",
    body: JSON.stringify(dados),
  })
    .then((r) => r.json())
    .then((r) => {
      const confirmacao = confirm(
        "Deseja realmente editar editora " + (dados.nome || ("de ID: " + dados.id)) + "?",
      );
      if (!confirmacao) return;
      alert(r.status + " - " + r.mensagem);
    })
    .catch((error) => {
      console.error("Erro: ", error);
    });
}

function adicionarLinhaTabela(corpo, linha) {
    let tr = document.createElement("tr");

    let id = linha.id;

    // Colunas e propriedades correspondentes
    const campos = {
        nomeEditora: "nome",
        cnpj: "cnpj",
        enderecoComercial: "endereco",
        telefoneContato: "telefone",
        valorMinimoPedido: "valor_minimo"
    };

    // Coluna do ID
    let tdId = document.createElement("td");
    tdId.textContent = id;
    tr.appendChild(tdId);

    // Criação dos inputs
    Object.entries(campos).forEach(([nome, propriedade]) => {
        let td = document.createElement("td");
        let input = document.createElement("input");

        input.name = nome + id;
        input.value = linha[propriedade];

        td.appendChild(input);
        tr.appendChild(td);
    });

    // Coluna de ações
    let tdAcaoEditar = document.createElement("td");
    let linkEditar = document.createElement("button");
    let tdAcaoExcluir = document.createElement("td");
    let linkExcluir = document.createElement("button");

    linkEditar.textContent = "Editar";

    linkEditar.addEventListener("click", function(event) {
        event.preventDefault();
        editar({ id: id, nome: tr.querySelector(`[name="nomeEditora${id}"]`).value, cnpj: tr.querySelector(`[name="cnpj${id}"]`).value, endereco: tr.querySelector(`[name="enderecoComercial${id}"]`).value, telefone: tr.querySelector(`[name="telefoneContato${id}"]`).value, valor_minimo: tr.querySelector(`[name="valorMinimoPedido${id}"]`).value });
    });

    tdAcaoEditar.appendChild(linkEditar);
    linkExcluir.textContent = "Excluir";
    linkExcluir.addEventListener("click", function(event) {
        event.preventDefault();
        excluir(linha);
    });
    tdAcaoExcluir.appendChild(linkExcluir);
    tr.appendChild(tdAcaoEditar);
    tr.appendChild(tdAcaoExcluir);

    corpo.appendChild(tr);
}

function atualizarTabela() {
  fetch("editora.php", { method: "GET" })
    .then((r) => r.json())
    .then((r) => {
      if (corpoTabela.children.length > 0) corpoTabela.replaceChildren();
      r.dados.forEach((valores) => adicionarLinhaTabela(corpoTabela, valores));
    })
    .catch((error) => console.error("Erro: ", error));
}

function excluir(linha){
  fetch("editora.php", {
    method: "DELETE",
    body: JSON.stringify({ id: linha.id })
  })
    .then((r) => r.json())
    .then((r) => {
      const confirmacao = confirm("Deseja realmente excluir a editora " + linha.nome + "?");
      if (!confirmacao) return;
      atualizarTabela();
    })
    .catch((error) => {
      console.error("Erro: ", error);
    });
}
