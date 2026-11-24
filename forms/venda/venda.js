const form = document.querySelector("#cadastro");
const pesquisar = document.querySelector("#btn-pesquisar");
const corpoTabela = document.getElementById("corpoTabela");
const msgVazia = document.getElementById("tabela-vazia");

const selectCliente = document.getElementById("idCliente");
const itensContainer = document.getElementById("itensVendaContainer");
const btnAdicionarItem = document.getElementById("btnAdicionarItem");

const opcoesPagamento = [
  { valor: "dinheiro", label: "Dinheiro" },
  { valor: "pix", label: "Pix" },
  { valor: "cartao_credito", label: "Cartão de Crédito" },
  { valor: "cartao_debito", label: "Cartão de Débito" },
  { valor: "boleto", label: "Boleto" },
];

let listaClientes = [];
let listaEstoque = [];

// Abrir modais
document.querySelectorAll("[data-abrir]").forEach((botao) => {
  botao.addEventListener("click", async () => {
    if (botao.dataset.abrir === "modal") {
      await carregarClientesEEstoque();
      if (itensContainer.children.length === 0) {
        adicionarLinhaItem();
      }
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

async function carregarClientesEEstoque() {
  try {
    const [resCli, resEst] = await Promise.all([
      fetch("../cliente/cliente.php", { method: "GET" }).then((r) => r.json()),
      fetch("../estoque/estoque.php", { method: "GET" }).then((r) => r.json()),
    ]);

    listaClientes = resCli.dados || [];
    listaEstoque = resEst.dados || [];

    const clienteAtual = selectCliente.value;
    selectCliente.innerHTML = '<option value="">Selecione o Cliente</option>';
    listaClientes.forEach((cli) => {
      const opt = document.createElement("option");
      opt.value = cli.id;
      opt.textContent = cli.nome;
      selectCliente.appendChild(opt);
    });
    if (clienteAtual) selectCliente.value = clienteAtual;

    // Atualiza selects de livros já existentes no formulário
    itensContainer.querySelectorAll("select[name='itemEstoque[]']").forEach((sel) => {
      const val = sel.value;
      preencherSelectEstoque(sel);
      if (val) sel.value = val;
    });

  } catch (error) {
    console.error("Erro ao carregar clientes/estoque: ", error);
  }
}

function preencherSelectEstoque(selectEl) {
  selectEl.innerHTML = '<option value="">Selecione o Livro</option>';
  listaEstoque.forEach((livro) => {
    const opt = document.createElement("option");
    opt.value = livro.id;
    opt.textContent = `${livro.nome} (Disp.: ${livro.qtde})`;
    selectEl.appendChild(opt);
  });
}

function adicionarLinhaItem() {
  const linha = document.createElement("div");
  linha.className = "item-venda-row";

  const selectLivro = document.createElement("select");
  selectLivro.name = "itemEstoque[]";
  preencherSelectEstoque(selectLivro);

  const inputQtd = document.createElement("input");
  inputQtd.type = "number";
  inputQtd.name = "itemQuantidade[]";
  inputQtd.min = "1";
  inputQtd.value = "1";
  inputQtd.title = "Quantidade";

  const btnRemover = document.createElement("button");
  btnRemover.type = "button";
  btnRemover.className = "botao-remover-item";
  btnRemover.textContent = "X";
  btnRemover.title = "Remover item";
  btnRemover.addEventListener("click", () => {
    if (itensContainer.children.length > 1) {
      linha.remove();
    } else {
      alert("A venda precisa ter pelo menos um item.");
    }
  });

  linha.appendChild(selectLivro);
  linha.appendChild(inputQtd);
  linha.appendChild(btnRemover);
  itensContainer.appendChild(linha);
}

btnAdicionarItem.addEventListener("click", () => {
  adicionarLinhaItem();
});

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const dados = new FormData(e.target);

  await fetch("venda.php", {
    method: "POST",
    body: dados,
  })
    .then((r) => r.json())
    .then(async (r) => {
      alert(r.status + " - " + r.mensagem);
      if (r.status === "Sucesso") {
        form.reset();
        itensContainer.replaceChildren();
        await carregarClientesEEstoque();
        adicionarLinhaItem();
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
  await carregarClientesEEstoque();

  fetch("venda.php", { method: "GET" })
    .then((r) => r.json())
    .then((r) => {
      corpoTabela.replaceChildren();
      (r.dados || []).forEach((valores) => adicionarLinhaTabela(corpoTabela, valores));
      atualizarVazio();
    })
    .catch((error) => console.error("Erro: ", error));
}

function editar(dados) {
  const confirmacao = confirm("Deseja realmente editar a venda de ID: " + dados.id + "?");
  if (!confirmacao) return;

  fetch("venda.php", {
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
    "Deseja realmente excluir a venda " + linha.id + "? Os itens serão devolvidos ao estoque.",
  );
  if (!confirmacao) return;

  fetch("venda.php", {
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

function formatarDataParaInput(dataMysql) {
  if (!dataMysql) return "";
  return dataMysql.replace(" ", "T").slice(0, 16);
}

function adicionarLinhaTabela(corpo, linha) {
  const tr = document.createElement("tr");
  const id = linha.id;

  const tdId = document.createElement("td");
  tdId.textContent = id;
  tr.appendChild(tdId);

  // Cliente (select)
  const tdCliente = document.createElement("td");
  const selCliente = document.createElement("select");
  selCliente.name = "idCliente" + id;
  listaClientes.forEach((cli) => {
    const opt = document.createElement("option");
    opt.value = cli.id;
    opt.textContent = cli.nome;
    if (String(cli.id) === String(linha.id_cliente)) opt.selected = true;
    selCliente.appendChild(opt);
  });
  tdCliente.appendChild(selCliente);
  tr.appendChild(tdCliente);

  // Data venda
  const tdData = document.createElement("td");
  const inputData = document.createElement("input");
  inputData.type = "datetime-local";
  inputData.name = "dataVenda" + id;
  inputData.value = formatarDataParaInput(linha.data_venda);
  tdData.appendChild(inputData);
  tr.appendChild(tdData);

  // Forma de pagamento (select)
  const tdPagamento = document.createElement("td");
  const selPagamento = document.createElement("select");
  selPagamento.name = "formaPagamento" + id;
  opcoesPagamento.forEach((op) => {
    const opt = document.createElement("option");
    opt.value = op.valor;
    opt.textContent = op.label;
    if (op.valor === linha.pagamento) opt.selected = true;
    selPagamento.appendChild(opt);
  });
  tdPagamento.appendChild(selPagamento);
  tr.appendChild(tdPagamento);

  // Desconto
  const tdDesconto = document.createElement("td");
  const inputDesconto = document.createElement("input");
  inputDesconto.type = "number";
  inputDesconto.step = "0.01";
  inputDesconto.name = "desconto" + id;
  inputDesconto.value = linha.desconto ?? "0";
  tdDesconto.appendChild(inputDesconto);
  tr.appendChild(tdDesconto);

  // Cupom fiscal
  const tdCupom = document.createElement("td");
  const inputCupom = document.createElement("input");
  inputCupom.type = "number";
  inputCupom.name = "cupomFiscal" + id;
  inputCupom.value = linha.cupom_fiscal ?? "";
  tdCupom.appendChild(inputCupom);
  tr.appendChild(tdCupom);

  // Itens vendidos
  const tdItens = document.createElement("td");
  const listaItens = linha.itens || [];
  if (listaItens.length > 0) {
    tdItens.textContent = listaItens
      .map((item) => `${item.nome_livro} (${item.quantidade}x)`)
      .join(", ");
  } else {
    tdItens.textContent = "—";
  }
  tr.appendChild(tdItens);

  // Botões Editar / Excluir
  const tdAcaoEditar = document.createElement("td");
  const btnEditar = document.createElement("button");
  btnEditar.textContent = "Editar";
  btnEditar.addEventListener("click", (event) => {
    event.preventDefault();
    editar({
      id: id,
      id_cliente: tr.querySelector(`[name="idCliente${id}"]`).value,
      data_venda: tr.querySelector(`[name="dataVenda${id}"]`).value,
      pagamento: tr.querySelector(`[name="formaPagamento${id}"]`).value,
      desconto: tr.querySelector(`[name="desconto${id}"]`).value,
      cupom_fiscal: tr.querySelector(`[name="cupomFiscal${id}"]`).value,
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
