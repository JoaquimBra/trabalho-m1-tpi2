const form = document.querySelector("#cadastro");
const pesquisar = document.querySelector("#btn-pesquisar");
const corpoTabela = document.getElementById("corpoTabela");
const msgVazia = document.getElementById("tabela-vazia");

// Abrir modais
document.querySelectorAll("[data-abrir]").forEach((botao) => {
  botao.addEventListener("click", () => {
    document.getElementById(botao.dataset.abrir).showModal();
  });
});

// Fechar pelo botão × e ao clicar fora da caixa
document.querySelectorAll("dialog.modal").forEach((dialog) => {
  dialog
    .querySelector(".botao-fechar-modal")
    .addEventListener("click", () => dialog.close());

  dialog.addEventListener("click", (e) => {
    const area = dialog.getBoundingClientRect();
    const dentro =
      e.clientX >= area.left &&
      e.clientX <= area.right &&
      e.clientY >= area.top &&
      e.clientY <= area.bottom;
    if (!dentro) dialog.close();
  });
});

// Mostra/esconde a mensagem de tabela vazia
function atualizarVazio() {
  msgVazia.hidden = corpoTabela.rows.length > 0;
}

atualizarVazio();

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const dados = new FormData(e.target);

  await fetch("fornecedor.php", {
    method: "POST",
    body: dados,
  })
    .then((r) => r.json())
    .then((r) => {
      alert(r.status + " - " + r.mensagem);
      if (r.status === "Sucesso") {
        form.reset();
      }
    })
    .catch((error) => {
      console.error("Erro: ", error);
    });
});

pesquisar.addEventListener("click", () => {
  atualizarTabela();
});

function atualizarTabela() {
  fetch("fornecedor.php", {
    method: "GET",
  })
    .then((r) => r.json())
    .then((r) => {
      corpoTabela.replaceChildren();
      (r.dados || []).forEach((valores) => {
        adicionarLinhaTabela(corpoTabela, valores);
      });
      atualizarVazio();
    })
    .catch((error) => {
      console.error("Erro: ", error);
    });
}

function editar(dados) {
  const confirmacao = confirm(
    "Deseja realmente editar o fornecedor " + dados.nome + "?",
  );
  if (!confirmacao) return;

  fetch("fornecedor.php", {
    method: "PUT",
    body: JSON.stringify(dados),
  })
    .then((r) => r.json())
    .then((r) => {
      alert(r.status + " - " + r.mensagem);
      if (r.status === "Sucesso") {
        atualizarTabela();
      }
    })
    .catch((error) => {
      console.error("Erro: ", error);
    });
}

function excluir(linha) {
  const confirmacao = confirm(
    "Deseja realmente excluir o fornecedor " + linha.nome + "?",
  );
  if (!confirmacao) return;

  fetch("fornecedor.php", {
    method: "DELETE",
    body: JSON.stringify({ id: linha.id }),
  })
    .then((r) => r.json())
    .then((r) => {
      if (r.status === "Erro") {
        alert(r.status + " - " + r.mensagem);
        return;
      }
      atualizarTabela();
    })
    .catch((error) => {
      console.error("Erro: ", error);
    });
}

function adicionarLinhaTabela(corpo, linha) {
  const tr = document.createElement("tr");
  const id = linha.id;

  const campos = {
    nomeEmpresa: "nome",
    emailPedidos: "email",
    prazoEntrega: "prazo_entrega",
    condicaoPagamento: "condicao_pagamento",
    valorPedido: "valor_minimo",
  };

  const tdId = document.createElement("td");
  tdId.textContent = id;
  tr.appendChild(tdId);

  Object.entries(campos).forEach(([nome, propriedade]) => {
    const td = document.createElement("td");
    const input = document.createElement("input");
    if (propriedade === "valor_minimo") {
      input.type = "number";
      input.step = "0.01";
    }
    input.name = nome + id;
    input.value = linha[propriedade] ?? "";
    td.appendChild(input);
    tr.appendChild(td);
  });

  const tdAcaoEditar = document.createElement("td");
  const linkEditar = document.createElement("button");
  linkEditar.textContent = "Editar";
  linkEditar.addEventListener("click", (event) => {
    event.preventDefault();
    editar({
      id: id,
      nome: tr.querySelector(`[name="nomeEmpresa${id}"]`).value,
      email: tr.querySelector(`[name="emailPedidos${id}"]`).value,
      prazo_entrega: tr.querySelector(`[name="prazoEntrega${id}"]`).value,
      condicao_pagamento: tr.querySelector(`[name="condicaoPagamento${id}"]`).value,
      valor_minimo: tr.querySelector(`[name="valorPedido${id}"]`).value,
    });
  });
  tdAcaoEditar.appendChild(linkEditar);

  const tdAcaoExcluir = document.createElement("td");
  const linkExcluir = document.createElement("button");
  linkExcluir.textContent = "Excluir";
  linkExcluir.addEventListener("click", (event) => {
    event.preventDefault();
    excluir(linha);
  });
  tdAcaoExcluir.appendChild(linkExcluir);

  tr.appendChild(tdAcaoEditar);
  tr.appendChild(tdAcaoExcluir);

  corpo.appendChild(tr);
}
