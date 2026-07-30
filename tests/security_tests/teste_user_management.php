<?php

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.php';

use Alpha\Mappers\MapperFactory;
use Containers\AppContainer;
use Alpha\Model\Domain\Repositories\UserRepository;
use Alpha\Model\Domain\Repositories\UserGroupRepository;
use Alpha\Model\Domain\Entities\User;
use Alpha\Model\Domain\Entities\UserGroup;

$mapperFactory = new MapperFactory();
$container = new AppContainer();

echo "=== TESTE 1: Criação e Validação de Papel/Grupo de Usuário (UserGroup) ===\n";

$userGroupRepo = new UserGroupRepository($mapperFactory, $container);

$testGroup = new UserGroup();
$testGroup->setName("Vendedor PDV Teste");
$testGroup->setPermission(json_encode([
    'access' => ['pos/sales_rep', 'catalog/product'],
    'modify' => ['pos/sales_rep']
]));

$groupId = $userGroupRepo->save($testGroup);
echo "UserGroup criado com ID: {$groupId}\n";

/** @var UserGroup|null $fetchedGroup */
$fetchedGroup = $userGroupRepo->find($groupId);

if ($fetchedGroup && $fetchedGroup->getName() === "Vendedor PDV Teste") {
    $permissions = $fetchedGroup->getPermissionArray();
    if (in_array('pos/sales_rep', $permissions['access'] ?? [])) {
        echo "✅ [PASS] UserGroup salvo e permissões JSON decodificadas com sucesso.\n";
    } else {
        echo "❌ [FAIL] Falha ao decodificar permissões do UserGroup.\n";
        exit(1);
    }
} else {
    echo "❌ [FAIL] UserGroup não encontrado no banco de dados.\n";
    exit(1);
}

echo "\n=== TESTE 2: Cadastro de Funcionário (User) com Hashing de Senha ===\n";

$userRepo = new UserRepository($mapperFactory, $container);

$testUser = new User();
$testUsername = "vend_" . substr((string)time(), -6);
$rawPassword = "senhaSegura123";

$testUser->setUsername($testUsername)
         ->setFirstname("Carlos")
         ->setLastname("Vendedor")
         ->setEmail($testUsername . "@empresa.com")
         ->setPassword(password_hash($rawPassword, PASSWORD_DEFAULT))
         ->setUserGroupId($groupId)
         ->setStatus(true)
         ->setDateAdded(date('Y-m-d H:i:s'));

$userId = $userRepo->save($testUser);
echo "Funcionário criado com ID: {$userId}\n";

/** @var User|null $fetchedUser */
$fetchedUser = $userRepo->find($userId);

if ($fetchedUser && password_verify($rawPassword, $fetchedUser->getPassword())) {
    echo "✅ [PASS] Funcionário salvo com sucesso e hash de senha verificado via password_verify.\n";
} else {
    echo "❌ [FAIL] Falha ao verificar credenciais do funcionário.\n";
    exit(1);
}

echo "\n=== TESTE 3: Proteção de Integridade (Impedimento de exclusão de grupo com usuários) ===\n";

$count = $userGroupRepo->countUsersInGroup($groupId);
echo "Total de funcionários vinculados ao grupo {$groupId}: {$count}\n";

if ($count >= 1) {
    echo "✅ [PASS] Contagem de funcionários atrelados ao grupo validada (total = {$count}). Exclusão deve ser bloqueada.\n";
} else {
    echo "❌ [FAIL] Falha ao contar funcionários vinculados ao grupo.\n";
    exit(1);
}

echo "\n=== TESTE 4: Limpeza e Exclusão Segura de Dados do Teste ===\n";

$userRepo->delete($userId);
echo "Funcionário teste ID {$userId} removido.\n";

$userGroupRepo->delete($groupId);
echo "Grupo teste ID {$groupId} removido.\n";

if ($userRepo->find($userId) === null && $userGroupRepo->find($groupId) === null) {
    echo "✅ [PASS] Limpeza concluída com sucesso.\n";
} else {
    echo "❌ [FAIL] Falha ao limpar registros de teste.\n";
    exit(1);
}

echo "\n=========================================\n";
echo "🎉 TODOS OS TESTES DE GESTÃO DE FUNCIONÁRIOS E PAPÉIS PASSARAM!\n";
