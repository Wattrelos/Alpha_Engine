Documento de progresso gerado pelo Gemini Code Assistent. Muito útil para aprender a programar e acompanhar a evolução do projeto.
Também ajuda a se reconectar ao que se estava fazendo, pois, se ficar muito tempo afastado do código, com estas anotações ficará fácil lembrar de onde parou:

Excelente escolha! Centralizar essas consultas em Mappers dentro do core/ permite que você reutilize a lógica tanto no OpenCart quanto nos seus testes unitários, mantendo o padrão de nomenclatura que definimos (PascalCase).

Aqui está a implementação do novo Mapper e a atualização da biblioteca de Pesos.


# O que foi feito:
1. Criação de WeightClassMapper.php:
    a. Utiliza o QueryBuilder para montar a consulta de forma segura e legível.
    b. Respeita a regra de negócio: Tabela pai usa id, tabela filha usa weight_class_id.
    c. Faz o SELECT wc.* para pegar o value e o id da tabela principal, e traz title e unit da descrição.
2. Atualização de weight.php:
    a. Importação do namespace Alpha\Mappers\WeightClassMapper.
    b. Limpeza do construtor: Removemos os comentários antigos e instanciamos o novo Mapper.
    c. Atualização do loop foreach foi ajustado para ler diretamente o array retornado pelo DAO ($results).
    d. Note que usei $result['id'] como chave, seguindo a sua regra de que as PKs agora se chamam id.
Como você já configurou o Composer e o Autoloader no index.php, o OpenCart conseguirá carregar a classe WeightClassMapper automaticamente assim que a biblioteca Weight for instanciada pelo sistema.