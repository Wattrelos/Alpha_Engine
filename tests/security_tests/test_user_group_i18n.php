<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../config.php';

\Containers\AppBootstrap::boot();

use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\UserGroupRepository;
use Alpha\Model\Domain\Entities\UserGroup;

echo "=== TESTE DE INTERNACIONALIZAÇÃO DE USER GROUPS (agsc_user_group_description) ===\n\n";

/** @var UserGroupRepository $userGroupRepo */
$userGroupRepo = RepositoryFactory::getInstance()->get(UserGroupRepository::class);

// 1. Testar Listagem Multilíngue (Language ID 1 - English vs Language ID 2 - PT-BR)
echo "--- 1. Testando busca por idioma ---\n";
$groupsEN = $userGroupRepo->findAllWithLanguage(1);
$groupsPT = $userGroupRepo->findAllWithLanguage(2);

echo "Total grupos encontrados (EN): " . count($groupsEN) . "\n";
echo "Total grupos encontrados (PT): " . count($groupsPT) . "\n";

foreach ($groupsEN as $g) {
    if ($g->getId() === 2) { // Demonstration group
        echo "Grupo ID 2 (EN): Name = '" . $g->getName() . "'\n";
    }
}
foreach ($groupsPT as $g) {
    if ($g->getId() === 2) {
        echo "Grupo ID 2 (PT): Name = '" . $g->getName() . "'\n";
    }
}

// 2. Testar Inserção de Novo Grupo com Descrições Multilíngues
echo "\n--- 2. Testando criação de novo grupo multilíngue ---\n";
$newGroup = new UserGroup();
$newGroup->setName("Grupo Teste Temp");
$newGroup->setPermission(json_encode(['access' => ['dashboard'], 'modify' => []]));

$namesByLanguage = [
    1 => 'Test Role (EN)',
    2 => 'Papel de Teste (PT)',
    3 => 'Rôle de Test (FR)'
];

$newGroupId = $userGroupRepo->saveWithDescriptions($newGroup, $namesByLanguage);
echo "Novo grupo criado com ID: {$newGroupId}\n";

assert($newGroupId > 0, "Erro ao criar novo grupo!");

// Limpa o IdentityMap do DAO para forçar recarga dos dados do BD
$userGroupRepo->clearIdentityMap();

// Verificação no repositório
$loadedEN = $userGroupRepo->findWithLanguage($newGroupId, 1);
$userGroupRepo->clearIdentityMap();
$loadedPT = $userGroupRepo->findWithLanguage($newGroupId, 2);
$userGroupRepo->clearIdentityMap();
$loadedFR = $userGroupRepo->findWithLanguage($newGroupId, 3);

echo "Carregado (EN): '" . $loadedEN->getName() . "'\n";
echo "Carregado (PT): '" . $loadedPT->getName() . "'\n";
echo "Carregado (FR): '" . $loadedFR->getName() . "'\n";

assert($loadedEN->getName() === 'Test Role (EN)', "Falha na tradução EN! Obtido: " . $loadedEN->getName());
assert($loadedPT->getName() === 'Papel de Teste (PT)', "Falha na tradução PT! Obtido: " . $loadedPT->getName());
assert($loadedFR->getName() === 'Rôle de Test (FR)', "Falha na tradução FR! Obtido: " . $loadedFR->getName());

// 3. Testar Atualização das Descrições
echo "\n--- 3. Testando atualização de descrições ---\n";
$updatedNames = [
    1 => 'Updated Role (EN)',
    2 => 'Papel Atualizado (PT)'
];
$userGroupRepo->saveWithDescriptions($loadedEN, $updatedNames);

// Limpa o IdentityMap do DAO para forçar recarga dos dados atualizados
$userGroupRepo->clearIdentityMap();

$reloadedEN = $userGroupRepo->findWithLanguage($newGroupId, 1);
$userGroupRepo->clearIdentityMap();
$reloadedPT = $userGroupRepo->findWithLanguage($newGroupId, 2);

echo "Atualizado (EN): '" . $reloadedEN->getName() . "'\n";
echo "Atualizado (PT): '" . $reloadedPT->getName() . "'\n";

assert($reloadedEN->getName() === 'Updated Role (EN)', "Falha na atualização EN!");
assert($reloadedPT->getName() === 'Papel Atualizado (PT)', "Falha na atualização PT!");

// 4. Testar Exclusão e Limpeza das Descrições
echo "\n--- 4. Testando exclusão e limpeza em cascata ---\n";
$deleteResult = $userGroupRepo->delete($newGroupId);
echo "Resultado exclusão: " . ($deleteResult ? "SUCESSO" : "FALHA") . "\n";

$descriptionsLeft = $userGroupRepo->findDescriptions($newGroupId);
echo "Descrições remanescentes no BD para ID {$newGroupId}: " . count($descriptionsLeft) . "\n";

assert(count($descriptionsLeft) === 0, "Descrições não foram excluídas corretamente!");

echo "\n=== TODOS OS TESTES PASSARAM COM SUCESSO! ===\n";
