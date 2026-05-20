<?php

namespace Alpha\Tests;

use Alpha\Mappers\EntityMappers\AddressMapper;

/**
 * TestAddressIndexJson - Validação unitária da recuperação de endereços.
 * 
 * Este teste simula a execução do AddressMapper para verificar a integridade
 * do QueryBuilder e a hidratação da entidade AddressFormat.
 */
// class TestAddressIndexJson // Comentar para executar diretamente.
// {
    /**
     * Executa a simulação da consulta getAddress(32, 2).
     */
    function index(): void
    {
        try {
            $addressMapper = new AddressMapper();

            header('Content-Type: application/json; charset=utf-8');

            // Alpha Engine: Recupera o endereço 32 com idioma 2 (Português/Brasil)
            $address = $addressMapper->getAddress(32, 2);

            // Tratamento de mensagens para resultados vazios ou não encontrados
            if (!$address) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Nenhum endereço encontrado para os critérios informados (ID: 32, Language: 2). Verifique se os dados de teste existem no banco de dados.'
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }

            // Configura o cabeçalho e retorna o JSON formatado
            echo json_encode($address, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            echo json_encode(['error' => true, 'message' => $e->getMessage()]);
        }
    }

   
// }
index();