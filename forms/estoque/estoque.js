const form = document.querySelector("#cadastro");
const pesquisar = document.querySelector("#btn-pesquisar");
const corpoTabela = document.getElementById("corpoTabela");
const msgVazia = document.getElementById("tabela-vazia");

const selectEditora = document.getElementById("idEditora");
const selectAutor = document.getElementById("idAutor");
const selectFornecedor = document.getElementById("idFornecedor");

let listaEditoras = [];
let listaAutores = [];
let listaFornecedores = [];

// Abrir modais
document.querySelectorAll("[data-abrir]").forEach((botao) => {
  botao.addEventListener("click", async () => {
    if (botao.dataset.abrir === "modal") {
      await carregarRelacionamentos();
    }
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

function preencherSelect(selectEl, lista, textoPadrao) {
  const valorAnterior = selectEl.value;
  selectEl.innerHTML = "";

  const optPadrao = document.createElement("option");
  optPadrao.value = "";
  optPadrao.textContent = textoPadrao;
  selectEl.appendChild(optPadrao);

  lista.forEach((item) => {
    const opt = document.createElement("option");
    opt.value = item.id;
    opt.textContent = item.nome;
    selectEl.appendChild(opt);
  });

  if (valorAnterior) {
    selectEl.value = valorAnterior;
  }
}

async function carregarRelacionamentos() {
  try {
    const [resEd, resAu, resFo] = await Promise.all([
      fetch("../editora/editora.php", { method: "GET" }).then((r) => r.json()),
      fetch("../autor/autor.php", { method: "GET" }).then((r) => r.json()),
      fetch("../fornecedor/fornecedor.php", { method: "GET" }).then((r) => r.json()),
    ]);

    listaEditoras = resEd.dados || [];
    listaAutores = resAu.dados || [];
    listaFornecedores = resFo.dados || [];

    preencherSelect(selectEditora, listaEditoras, "Selecione a Editora");
    preencherSelect(selectAutor, listaAutores, "Selecione o Autor");
    preencherSelect(selectFornecedor, listaFornecedores, "Selecione o Fornecedor");
  } catch (error) {
    console.error("Erro ao carregar relacionamentos: ", error);
  }
}

function criarSelectTabela(lista, valorSelecionado, nomeCampo) {
  const select = document.createElement("select");
  select.name = nomeCampo;

  lista.forEach((item) => {
    const opt = document.createElement("option");
    opt.value = item.id;
    opt.textContent = item.nome;
    if (String(item.id) === String(valorSelecionado)) {
      opt.selected = true;
    }
    select.appendChild(opt);
  });

  return select;
}

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

async function atualizarTabela() {
  await carregarRelacionamentos();

  fetch("estoque.php", { method: "GET" })
    .then((r) => r.json())
    .then((r) => {
      corpoTabela.replaceChildren();
      (r.dados || []).forEach((valores) => adicionarLinhaTabela(corpoTabela, valores));
      atualizarVazio();
    })
    .catch((error) => console.error("Erro: ", error));
}

function editar(dados) {
  const confirmacao = confirm(
    "Deseja realmente editar o item de estoque " + (dados.nome || "de ID: " + dados.id) + "?",
  );
  if (!confirmacao) return;

  fetch("estoque.php", {
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
  const confirmacao = confirm("Deseja realmente excluir o item de estoque " + linha.nome + "?");
  if (!confirmacao) return;

  fetch("estoque.php", {
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

function criarTdInput(name, value, type = "text") {
  const td = document.createElement("td");
  const input = document.createElement("input");
  input.type = type;
  input.name = name;
  input.value = value ?? "";
  td.appendChild(input);
  return td;
}

function adicionarLinhaTabela(corpo, linha) {
  const tr = document.createElement("tr");
  const id = linha.id;

  const tdId = document.createElement("td");
  tdId.textContent = id;
  tr.appendChild(tdId);

  tr.appendChild(criarTdInput("tituloLivro" + id, linha.nome));
  tr.appendChild(criarTdInput("isbn" + id, linha.isbn));

  const tdEditora = document.createElement("td");
  tdEditora.appendChild(criarSelectTabela(listaEditoras, linha.id_editora, "idEditora" + id));
  tr.appendChild(tdEditora);

  const tdAutor = document.createElement("td");
  tdAutor.appendChild(criarSelectTabela(listaAutores, linha.id_autor, "idAutor" + id));
  tr.appendChild(tdAutor);

  const tdFornecedor = document.createElement("td");
  tdFornecedor.appendChild(criarSelectTabela(listaFornecedores, linha.id_fornecedor, "idFornecedor" + id));
  tr.appendChild(tdFornecedor);

  tr.appendChild(criarTdInput("quantidade" + id, linha.qtde, "number"));
  tr.appendChild(criarTdInput("quantidadeMinima" + id, linha.qtde_minima, "number"));
  tr.appendChild(criarTdInput("localizacao" + id, linha.localizacao));
  tr.appendChild(criarTdInput("dataReposicao" + id, linha.data_reposicao, "date"));
  tr.appendChild(criarTdInput("custoUnitario" + id, linha.custo_unitario, "number"));

  const tdAcaoEditar = document.createElement("td");
  const btnEditar = document.createElement("button");
  btnEditar.textContent = "Editar";
  btnEditar.addEventListener("click", (event) => {
    event.preventDefault();
    editar({
      id: id,
      nome: tr.querySelector(`[name="tituloLivro${id}"]`).value,
      isbn: tr.querySelector(`[name="isbn${id}"]`).value,
      id_editora: tr.querySelector(`[name="idEditora${id}"]`).value,
      id_autor: tr.querySelector(`[name="idAutor${id}"]`).value,
      id_fornecedor: tr.querySelector(`[name="idFornecedor${id}"]`).value,
      qtde: tr.querySelector(`[name="quantidade${id}"]`).value,
      qtde_minima: tr.querySelector(`[name="quantidadeMinima${id}"]`).value,
      localizacao: tr.querySelector(`[name="localizacao${id}"]`).value,
      data_reposicao: tr.querySelector(`[name="dataReposicao${id}"]`).value,
      custo_unitario: tr.querySelector(`[name="custoUnitario${id}"]`).value,
    });
  });
  tdAcaoEditar.appendChild(btnEditar);

  const tdAcaoExcluir = document.createElement("td");
  const btnExcluir = document.createElement("button");
  btnExcluir.textContent = "Excluir";
  btnExcluir.addEventListener("click", (event) => {
    event.preventDefault();
    excluir(linha);
  });
  tdAcaoExcluir.appendChild(btnExcluir);

  tr.appendChild(tdAcaoEditar);
  tr.appendChild(tdAcaoExcluir);

  corpo.appendChild(tr);
}
