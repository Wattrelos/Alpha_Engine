SELECT c.id AS cidade_id,
    c.nome AS cidade_nome,
    e.codigo_subdivisao AS estado,
    p.iso_alpha2 AS pais
FROM cidades c
    JOIN estados e ON c.estado_id = e.id
    JOIN paises p ON e.pais_id = p.id
WHERE p.iso_alpha2 = 'BR'
    AND e.codigo_subdivisao = 'SP'
    AND c.nome = 'Itaquaquecetuba';