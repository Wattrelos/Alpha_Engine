<?php

namespace Alpha\Controller\Actions\Setup;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment as TwigEnvironment;
use Alpha\Support\EnvironmentManager;

/**
 * ShowSetupAction - Renderiza o Assistente Visual de Instalação (Setup Wizard).
 */
class ShowSetupAction
{
    private TwigEnvironment $twig;
    private EnvironmentManager $envManager;

    public function __construct(TwigEnvironment $twig, ?EnvironmentManager $envManager = null)
    {
        $this->twig = $twig;
        $this->envManager = $envManager ?? new EnvironmentManager();
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $envPath = $this->envManager->getEnvPath();
        $envWritable = is_writable(dirname($envPath)) && (!file_exists($envPath) || is_writable($envPath));
        $storageDir = defined('DIR_STORAGE') ? DIR_STORAGE : realpath(__DIR__ . '/../../../../storage') . '/';
        $storageWritable = is_dir($storageDir) && is_writable($storageDir);

        // Verificação de requisitos de ambiente
        $requirements = [
            'php_version' => [
                'name'    => 'PHP >= 8.1',
                'success' => version_compare(PHP_VERSION, '8.1.0', '>='),
                'current' => PHP_VERSION,
            ],
            'pdo_mysql' => [
                'name'    => 'Extensão PDO MySQL',
                'success' => extension_loaded('pdo_mysql'),
                'current' => extension_loaded('pdo_mysql') ? 'Instalado' : 'Ausente',
            ],
            'mbstring' => [
                'name'    => 'Extensão MBString',
                'success' => extension_loaded('mbstring'),
                'current' => extension_loaded('mbstring') ? 'Instalado' : 'Ausente',
            ],
            'gd' => [
                'name'    => 'Extensão GD ou Imagick',
                'success' => extension_loaded('gd') || extension_loaded('imagick'),
                'current' => (extension_loaded('gd') || extension_loaded('imagick')) ? 'Instalado' : 'Ausente',
            ],
            'env_writable' => [
                'name'    => 'Permissão de Escrita (.env)',
                'success' => $envWritable,
                'current' => $envWritable ? 'Gravável' : 'Sem Permissão',
            ],
            'storage_writable' => [
                'name'    => 'Permissão de Escrita (storage/)',
                'success' => $storageWritable,
                'current' => $storageWritable ? 'Gravável' : 'Sem Permissão',
            ]
        ];

        $allRequirementsPassed = true;
        foreach ($requirements as $req) {
            if (!$req['success']) {
                $allRequirementsPassed = false;
                break;
            }
        }

        $html = $this->twig->render('setup/installer.html.twig', [
            'requirements'            => $requirements,
            'all_requirements_passed' => $allRequirementsPassed,
            'default_host'            => $_ENV['DB_HOSTNAME'] ?? '127.0.0.1',
            'default_port'            => $_ENV['DB_PORT'] ?? '3306',
            'default_user'            => $_ENV['DB_USERNAME'] ?? 'root',
            'default_db'              => $_ENV['DB_DATABASE'] ?? 'AlphaAgsonhos',
            'default_prefix'          => $_ENV['DB_PREFIX'] ?? 'agsc_',
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
