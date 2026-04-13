# Resultados DMA - Documentacao dos calculos

Este documento descreve como o relatorio administrativo de Resultados DMA calcula as colunas exibidas na tela e no Excel.

O fluxo principal esta em `ResultsController::buildReportData()`.

## 1. Escopo do relatorio

O relatorio busca registros da tabela DMA filtrando:

- lojas selecionadas;
- data inicial e data final;
- `app_product_id = 1`.

Dentro desse conjunto, cada linha do DMA e tratada como:

- `Saida`: compoe as colunas de saidas e os valores previstos;
- `Entrada`: compoe as colunas de entradas e os valores realizados.

## 2. Fontes usadas nos calculos

### 2.1. DMA

Usado para:

- quantidade e valor de saidas;
- quantidade e valor de entradas;
- kg e valor realizado por tipo de corte;
- custo efetivo do registro.

### 2.2. ExpectedYield

Usado apenas para registros de `Saida`.

Para cada combinacao de:

- loja;
- mercadoria (`good_code`);

o sistema busca os percentuais esperados de rendimento:

- `prime`;
- `second`;
- `bones_skin`;
- `bones_discard`.

No relatorio atual sao usados:

- `prime` para Primeira;
- `second` para Segunda;
- `bones_skin` para Osso e Pelanca.

### 2.3. Produtos de venda

As colunas dinamicas de MAP de vendas sao calculadas a partir de `ProductsSells`, somadas por:

- loja;
- produto;
- periodo filtrado.

## 3. Como o custo e definido

Cada registro do DMA usa o seguinte custo efetivo:

1. se `Dma.cost` estiver preenchido, ele e usado;
2. se nao estiver, o sistema usa fallback da mercadoria:
   - se `opcusto = 'M'`, usa `customed`;
   - caso contrario, usa `custotab`.

Formula conceitual:

```text
custo_efetivo = Dma.cost
se Dma.cost estiver vazio:
  custo_efetivo = customed, quando opcusto = 'M'
  custo_efetivo = custotab, nos demais casos
```

## 4. Como o custo medio por corte e calculado

Antes de montar o relatorio por loja, o sistema calcula um custo medio ponderado por tipo de corte, usando apenas registros de `Entrada` do periodo.

Tipos considerados:

- Primeira
- Segunda
- Osso e Pelanca

Formula por loja e por corte:

```text
custo_medio_corte = soma(custo_efetivo * quantidade) / soma(quantidade)
```

Esse valor alimenta as colunas:

- R$ Custo Medio de Primeira;
- R$ Custo Medio de Segunda;
- R$ Custo Medio de Osso e Pelanca.

## 5. Como cada bloco da tabela e calculado

## 5.1. SAIDAS

Somente registros DMA com `type = 'Saida'`.

### Kg

```text
total_saidas_kg = soma(quantity das saidas)
```

### R$

```text
total_saidas_rs = soma(quantity * custo_efetivo das saidas)
```

## 5.2. ENTRADAS

Somente registros DMA com `type = 'Entrada'`.

### Kg

```text
total_entradas_kg = soma(quantity das entradas)
```

### R$

```text
total_entradas_rs = soma(quantity * custo_efetivo das entradas)
```

### R$ Previstos

E a soma dos previstos de:

- Primeira;
- Segunda;
- Osso e Pelanca.

Formula:

```text
rendimento_esperado_total =
  rendimento_esperado_primeira +
  rendimento_esperado_segunda +
  rendimento_esperado_osso_pelanca
```

### % Atingida

Compara o realizado em entradas com o previsto total.

Formula:

```text
percentual_atingido = (total_entradas_rs / rendimento_esperado_total) * 100
```

Se o previsto total for zero, o percentual exibido e zero.

## 5.3. DIFERENCA

### Kg

```text
diferenca_saidas_entradas_kg = total_saidas_kg - total_entradas_kg
```

### R$

```text
diferenca_saidas_entradas_rs = total_saidas_rs - total_entradas_rs
```

## 5.4. PRIMEIRA

### R$ Custo Medio

E o custo medio ponderado das entradas do tipo `Primeira` no periodo.

### R$ Previstos

Para cada registro de `Saida`, o sistema:

1. busca o percentual `prime` da tabela `ExpectedYield`;
2. calcula a quantidade esperada de Primeira;
3. multiplica essa quantidade pelo custo medio de Primeira da loja no periodo.

Formula por registro de saida:

```text
qtd_esperada_primeira = quantidade_saida * (prime / 100)
valor_previsto_primeira = qtd_esperada_primeira * custo_medio_primeira
```

Formula consolidada por loja:

```text
rendimento_esperado_primeira = soma(valor_previsto_primeira de todas as saidas)
```

### R$ Realizados

Somente entradas cujo `cutout_type = 'Primeira'`.

```text
rendimento_executado_primeira = soma(quantity * custo_efetivo das entradas Primeira)
```

### R$ Diferenca

```text
rendimento_dif_primeira = rendimento_executado_primeira - rendimento_esperado_primeira
```

### Kg

```text
total_kg_primeira = soma(quantity das entradas Primeira)
```

## 5.5. SEGUNDA

Segue exatamente a mesma logica da Primeira, trocando:

- percentual `prime` por `second`;
- corte `Primeira` por `Segunda`.

Formulas:

```text
qtd_esperada_segunda = quantidade_saida * (second / 100)
valor_previsto_segunda = qtd_esperada_segunda * custo_medio_segunda
rendimento_esperado_segunda = soma(valor_previsto_segunda)
rendimento_executado_segunda = soma(quantity * custo_efetivo das entradas Segunda)
rendimento_dif_segunda = rendimento_executado_segunda - rendimento_esperado_segunda
total_kg_segunda = soma(quantity das entradas Segunda)
```

## 5.6. OSSO E PELANCA

Segue a mesma logica, usando `bones_skin` como percentual esperado e `Osso e Pelanca` como tipo de corte.

Formulas:

```text
qtd_esperada_osso_pelanca = quantidade_saida * (bones_skin / 100)
valor_previsto_osso_pelanca = qtd_esperada_osso_pelanca * custo_medio_osso_pelanca
rendimento_esperado_osso_pelanca = soma(valor_previsto_osso_pelanca)
rendimento_executado_osso_pelanca = soma(quantity * custo_efetivo das entradas Osso e Pelanca)
rendimento_dif_osso_pelanca = rendimento_executado_osso_pelanca - rendimento_esperado_osso_pelanca
total_kg_osso_pelanca = soma(quantity das entradas Osso e Pelanca)
```

## 5.7. Posicao Rank

O relatorio calcula uma base de ranking por loja:

```text
base_calc_rank = total_entradas_rs / rendimento_esperado_total
```

Depois ordena as lojas em ordem decrescente dessa base e atribui a posicao:

```text
posicao_rank = ordem da loja apos sort decrescente
```

## 5.8. MAP vendas

As colunas dinamicas de MAP mostram totais de vendas por produto no periodo filtrado.

Formula generica:

```text
map_venda_produto = soma(total em ProductsSells por loja + produto + periodo)
```

Os totais de grupo sao:

```text
map_sales_total_first_second = soma dos produtos do grupo Primeira/Segunda
map_sales_total_osso = soma dos produtos do grupo Osso e Pelanca
```

## 6. Caso 1: filtro de 1 dia

Quando a data inicial e igual a data final, os calculos usam somente os registros daquele dia.

Na pratica:

1. `Saidas` consideram apenas as saidas do dia.
2. `Entradas` consideram apenas as entradas do dia.
3. `Custo medio` e calculado apenas com as entradas do dia.
4. `R$ Previstos` e a soma dos previstos das saidas do dia.
5. `R$ Realizados` e a soma do realizado das entradas do dia.
6. A coluna `Finalizado por` aparece somente nesse caso.

Resumo conceitual:

```text
periodo = 1 dia
todos os somatorios usam somente esse dia
custo medio tambem usa somente esse dia
```

## 7. Caso 2: filtro de 2 ou mais dias

Quando a data inicial e diferente da data final, o relatorio trabalha sobre o periodo inteiro.

Na pratica:

1. `Saidas` somam todas as saidas do intervalo.
2. `Entradas` somam todas as entradas do intervalo.
3. `Custo medio` e recalculado considerando todas as entradas do intervalo, por loja e por corte.
4. `R$ Previstos` e a soma dos previstos gerados por todas as saidas do intervalo.
5. `R$ Realizados` e a soma dos valores de entrada do intervalo.
6. A coluna `Finalizado por` nao aparece.

Resumo conceitual:

```text
periodo = varios dias
todos os somatorios usam o intervalo completo
custo medio vira media ponderada do intervalo completo
previsto tambem e somado no intervalo completo
```

## 8. Diferenca pratica entre 1 dia e 2 ou mais dias

Depois da correcao aplicada no controller, a regra de negocio do previsto e a mesma nos dois cenarios:

```text
previsto = soma(qtd_esperada * custo_medio_do_periodo)
```

O que muda entre os dois casos nao e a formula-base, e sim a massa de dados usada:

### Em 1 dia

- a massa de dados e apenas um dia;
- o custo medio reflete apenas aquele dia;
- as saidas e entradas sao apenas daquele dia.

### Em 2 ou mais dias

- a massa de dados e o intervalo inteiro;
- o custo medio vira uma media ponderada do intervalo;
- as saidas e entradas sao a soma do intervalo.

## 9. Observacao importante sobre os totais do rodape

No rodape:

- varios campos sao soma das lojas;
- `atingida_media` e media simples do percentual das lojas;
- `custo_med_primeira_media`, `custo_med_segunda_media` e `custo_med_osso_pelanca_media` sao medias simples entre lojas.

Exemplo:

```text
atingida_media = soma(percentual de cada loja) / quantidade_de_lojas
```

Isso significa que o rodape mistura:

- totais absolutos para colunas de valor e quantidade;
- medias para colunas marcadas com `*`.

## 10. Leitura rapida das principais formulas

```text
total_saidas_kg = soma(saidas.quantity)
total_saidas_rs = soma(saidas.quantity * custo_efetivo_saida)

total_entradas_kg = soma(entradas.quantity)
total_entradas_rs = soma(entradas.quantity * custo_efetivo_entrada)

custo_medio_corte = soma(custo_efetivo * quantidade) / soma(quantidade)

qtd_esperada_corte = quantidade_saida * (percentual_expected_yield / 100)
valor_previsto_corte = qtd_esperada_corte * custo_medio_corte
rendimento_esperado_corte = soma(valor_previsto_corte)

rendimento_esperado_total =
  rendimento_esperado_primeira +
  rendimento_esperado_segunda +
  rendimento_esperado_osso_pelanca

percentual_atingido = (total_entradas_rs / rendimento_esperado_total) * 100

diferenca_kg = total_saidas_kg - total_entradas_kg
diferenca_rs = total_saidas_rs - total_entradas_rs

rendimento_dif_corte = rendimento_executado_corte - rendimento_esperado_corte

base_calc_rank = total_entradas_rs / rendimento_esperado_total
```