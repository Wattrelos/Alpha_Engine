<?php
/*
* Como o PHP valida o bloqueio no Checkout
* Quando o cliente digita o CEP no front-end, seu gateway de CEP (como ViaCEP) retorna o nome da cidade.
* Seu código PHP precisará descobrir o GeoNames ID dela (usando a API do GeoNames) ou buscar pelo nome + UF no banco.
*/

// 1. Dados recebidos após a consulta do CEP (Simulação)


// Dados vindos da sua API de endereço externa
$cityName = "São Paulo";
$inputIsoCode = "BR-SP";

// Buscamos a cidade trazendo junto a verificação da surrogate key da zona
$sql = "SELECT c.id, c.is_served 
        FROM cities c
        JOIN zones z ON c.zone_id = z.id
        WHERE z.iso_code = :iso_code AND c.name = :city_name";

$stmt = $pdo->prepare($sql); // Ja temos conexão implementada.
$stmt->execute([
    'iso_code'  => $inputIsoCode,
    'city_name' => $cityName
]);
$city = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$city || $city['is_served'] == 0) {
    // Bloqueia o checkout se não atender
    // ...
}


// Se passou pela validação, o PHP continua o fluxo de fechamento do pedido...

/*
* Vantagem desta arquitetura para a expansão futura:
*   Ao criar a tabela `cidades` com a coluna `atendido`, você transforma a restrição de logística em uma regra de negócio do banco de dados.
*   Para adicionar suporte a uma nova cidade, basta inserir a cidade na tabela com `atendido = 1`. Você não precisa alterar o código PHP ou as regras de negócio em outros sistemas.
*   Qualquer aplicação que utilize este banco de dados (site, app, ERP) terá a mesma lógica de validação automaticamente.
*/
