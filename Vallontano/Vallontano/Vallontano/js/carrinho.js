const botaoCarrinho = document.querySelector("#carrinho");
const painel = document.querySelector("#painel-carrinho");
const lista = document.querySelector("#itens-carrinho");
const contador = document.querySelector("#contador");
const subtotal = document.querySelector("#subtotal");

let produtos = [];

botaoCarrinho.addEventListener("click", () => {
    painel.hidden = !painel.hidden;
});

document.querySelector("#finalizar-pedido").addEventListener("click", () => {
    if (produtos.length) {
        sessionStorage.setItem("carrinho", JSON.stringify(produtos));
        window.location.href = "checkout.php";
    }
});

document.querySelectorAll(".adicionar-carrinho").forEach(botao => {
    botao.addEventListener("click", () => {
        const id = Number(botao.dataset.id);
        const produto = produtos.find(item => item.id === id);

        if (produto) {
            produto.quantidade++;
        } else {
            produtos.push({
                id,
                nome: botao.dataset.nome,
                preco: Number(botao.dataset.preco),
                quantidade: 1
            });
        }

        atualizarCarrinho();
    });
});

function atualizarCarrinho() {
    contador.textContent = produtos.reduce((total, item) => total + item.quantidade, 0);

    lista.innerHTML = produtos.map(item => `
        <div class="item-carrinho">
            <p>${item.nome}</p>
            <p>${item.quantidade} × ${item.preco.toLocaleString("pt-BR", {
                style: "currency", currency: "BRL"
            })}</p>
            <button type="button" data-remover="${item.id}">Remover</button>
        </div>
    `).join("");

    subtotal.textContent = produtos.reduce(
        (total, item) => total + item.preco * item.quantidade, 0
    ).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

    lista.querySelectorAll("[data-remover]").forEach(botao => {
        botao.addEventListener("click", () => {
            const produto = produtos.find(item => item.id === Number(botao.dataset.remover));

            if (produto.quantidade > 1) {
                produto.quantidade--;
            } else {
                produtos = produtos.filter(item => item.id !== produto.id);
            }

            atualizarCarrinho();
        });
    });
}

        document.querySelector("#finalizar-pedido").addEventListener("click", () => {
        if (produtos.length) {
        sessionStorage.setItem("carrinho", JSON.stringify(produtos));
        window.location.href = "checkout.php";
    }
});