<?php
// Se solicitar phpinfo via parâmetro ?info=1
if (isset($_GET['info']) && $_GET['info'] === '1') {
    phpinfo();
    exit;
}

// Testes de Conexão com os Serviços do Docker
$services = [
    'mariadb' => ['status' => false, 'message' => '', 'latency' => 0],
    'redis'   => ['status' => false, 'message' => '', 'latency' => 0],
    'rabbitmq'=> ['status' => false, 'message' => '', 'latency' => 0]
];

// 1. Teste MariaDB (PDO)
$start = microtime(true);
try {
    $dbHost = getenv('DB_HOST') ?: 'mariadb';
    $dbPort = getenv('DB_PORT') ?: '3306';
    $dbName = getenv('DB_DATABASE') ?: 'saas_db';
    $dbUser = getenv('DB_USERNAME') ?: 'saas_user';
    $dbPass = getenv('DB_PASSWORD') ?: 'saas_password';

    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_TIMEOUT => 2,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $services['mariadb']['status'] = true;
    $services['mariadb']['message'] = "Conectado a {$dbName} (MariaDB 11)";
    $services['mariadb']['latency'] = round((microtime(true) - $start) * 1000, 2);
} catch (Exception $e) {
    $services['mariadb']['message'] = "Erro: " . $e->getMessage();
}

// 2. Teste Redis
$start = microtime(true);
try {
    if (class_exists('Redis')) {
        $redisHost = getenv('REDIS_HOST') ?: 'redis';
        $redisPort = (int)(getenv('REDIS_PORT') ?: 6379);

        $redis = new Redis();
        $connected = $redis->connect($redisHost, $redisPort, 2);
        if ($connected && $redis->ping()) {
            $services['redis']['status'] = true;
            $services['redis']['message'] = "Conectado a {$redisHost}:{$redisPort} (PONG)";
            $services['redis']['latency'] = round((microtime(true) - $start) * 1000, 2);
        } else {
            $services['redis']['message'] = "Falha ao responder PING";
        }
    } else {
        $services['redis']['message'] = "Extensão Redis não instalada";
    }
} catch (Exception $e) {
    $services['redis']['message'] = "Erro: " . $e->getMessage();
}

// 3. Teste RabbitMQ (AMQP)
$start = microtime(true);
try {
    if (class_exists('AMQPConnection')) {
        $amqpHost = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
        $amqpPort = (int)(getenv('RABBITMQ_PORT') ?: 5672);
        $amqpUser = getenv('RABBITMQ_USER') ?: 'guest';
        $amqpPass = getenv('RABBITMQ_PASSWORD') ?: 'guest';

        $amqp = new AMQPConnection([
            'host' => $amqpHost,
            'port' => $amqpPort,
            'vhost' => '/',
            'login' => $amqpUser,
            'password' => $amqpPass,
            'read_timeout' => 2
        ]);
        $amqp->connect();
        if ($amqp->isConnected()) {
            $services['rabbitmq']['status'] = true;
            $services['rabbitmq']['message'] = "Conectado ao Broker AMQP (Porta {$amqpPort})";
            $services['rabbitmq']['latency'] = round((microtime(true) - $start) * 1000, 2);
            $amqp->disconnect();
        } else {
            $services['rabbitmq']['message'] = "Falha de conexão com RabbitMQ";
        }
    } else {
        $services['rabbitmq']['message'] = "Extensão AMQP não instalada";
    }
} catch (Exception $e) {
    $services['rabbitmq']['message'] = "Erro: " . $e->getMessage();
}

$allHealthy = $services['mariadb']['status'] && $services['redis']['status'] && $services['rabbitmq']['status'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello World! - Beta Engine SaaS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #090d16;
            --bg-secondary: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent-cyan: #06b6d4;
            --accent-purple: #8b5cf6;
            --accent-blue: #3b82f6;
            --success-green: #10b981;
            --error-red: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Background ambient glow */
        .glow-sphere {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.45;
        }
        .glow-1 {
            width: 450px;
            height: 450px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));
            top: -100px;
            left: -100px;
        }
        .glow-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, var(--accent-purple), #ec4899);
            bottom: -100px;
            right: -100px;
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 960px;
            width: 100%;
        }

        /* Hero Header */
        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: var(--success-green);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--success-green);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(to right, #ffffff, #93c5fd, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.75rem;
        }

        p.lead {
            font-size: 1.125rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Services Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.4);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .service-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .icon-box {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .service-name {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-online {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .status-offline {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .card-body {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .latency-tag {
            display: inline-block;
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #6ee7b7;
            font-family: monospace;
        }

        /* Quick Links Section */
        .quick-access {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.5), rgba(15, 23, 42, 0.7));
            border: 1px solid var(--card-border);
            backdrop-filter: blur(16px);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .quick-title h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.15rem;
            margin-bottom: 0.25rem;
        }
        .quick-title p {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            color: white;
            border: none;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: var(--text-primary);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-2px);
        }

        footer {
            margin-top: 2.5rem;
            text-align: center;
            font-size: 0.825rem;
            color: #64748b;
        }

        @media (max-width: 640px) {
            h1 { font-size: 2.2rem; }
            .quick-access { flex-direction: column; text-align: center; }
            .btn-group { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="glow-sphere glow-1"></div>
    <div class="glow-sphere glow-2"></div>

    <div class="container">
        <div class="header">
            <div class="badge-pill">
                <span class="pulse-dot"></span>
                Ambiente Docker Rodando
            </div>
            <h1>Hello, World! 🚀</h1>
            <p class="lead">Bem-vindo ao <strong>Beta Engine SaaS</strong>. Seu stack completo está configurado e integrado com sucesso.</p>
        </div>

        <div class="grid">
            <!-- PHP Card -->
            <div class="card">
                <div class="card-header">
                    <div class="service-info">
                        <div class="icon-box">🐘</div>
                        <div>
                            <div class="service-name">PHP Backend</div>
                            <small style="color: var(--text-secondary);">PHP <?= phpversion() ?> FPM</small>
                        </div>
                    </div>
                    <span class="status-badge status-online">Ativo</span>
                </div>
                <div class="card-body">
                    Extensões carregadas: <strong>PDO MySQL</strong>, <strong>Redis</strong>, <strong>AMQP</strong>, <strong>OPcache</strong>.
                    <br>
                    <span class="latency-tag">FPM Porta 9000 &bull; Alpine Linux</span>
                </div>
            </div>

            <!-- MariaDB Card -->
            <div class="card">
                <div class="card-header">
                    <div class="service-info">
                        <div class="icon-box">🐬</div>
                        <div>
                            <div class="service-name">MariaDB</div>
                            <small style="color: var(--text-secondary);">Porta 3306</small>
                        </div>
                    </div>
                    <span class="status-badge <?= $services['mariadb']['status'] ? 'status-online' : 'status-offline' ?>">
                        <?= $services['mariadb']['status'] ? 'Conectado' : 'Erro' ?>
                    </span>
                </div>
                <div class="card-body">
                    <?= htmlspecialchars($services['mariadb']['message']) ?>
                    <?php if ($services['mariadb']['status']): ?>
                        <br><span class="latency-tag">Ping: <?= $services['mariadb']['latency'] ?> ms</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Redis Card -->
            <div class="card">
                <div class="card-header">
                    <div class="service-info">
                        <div class="icon-box">⚡</div>
                        <div>
                            <div class="service-name">Redis</div>
                            <small style="color: var(--text-secondary);">Porta 6379</small>
                        </div>
                    </div>
                    <span class="status-badge <?= $services['redis']['status'] ? 'status-online' : 'status-offline' ?>">
                        <?= $services['redis']['status'] ? 'Conectado' : 'Erro' ?>
                    </span>
                </div>
                <div class="card-body">
                    <?= htmlspecialchars($services['redis']['message']) ?>
                    <?php if ($services['redis']['status']): ?>
                        <br><span class="latency-tag">Ping: <?= $services['redis']['latency'] ?> ms</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RabbitMQ Card -->
            <div class="card">
                <div class="card-header">
                    <div class="service-info">
                        <div class="icon-box">🐇</div>
                        <div>
                            <div class="service-name">RabbitMQ</div>
                            <small style="color: var(--text-secondary);">Porta 5672 / 15672</small>
                        </div>
                    </div>
                    <span class="status-badge <?= $services['rabbitmq']['status'] ? 'status-online' : 'status-offline' ?>">
                        <?= $services['rabbitmq']['status'] ? 'Conectado' : 'Erro' ?>
                    </span>
                </div>
                <div class="card-body">
                    <?= htmlspecialchars($services['rabbitmq']['message']) ?>
                    <?php if ($services['rabbitmq']['status']): ?>
                        <br><span class="latency-tag">Ping: <?= $services['rabbitmq']['latency'] ?> ms</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Painéis de Acesso Rápido -->
        <div class="quick-access">
            <div class="quick-title">
                <h3>Painéis de Gerenciamento</h3>
                <p>Acesse as interfaces gráficas configuradas no seu Docker Compose</p>
            </div>
            <div class="btn-group">
                <a href="http://localhost:8080" target="_blank" class="btn btn-primary">
                    <span>🗄️ Abrir PHPMyAdmin</span>
                </a>
                <a href="http://localhost:15672" target="_blank" class="btn btn-secondary">
                    <span>🐇 Abrir RabbitMQ</span>
                </a>
                <a href="?info=1" target="_blank" class="btn btn-secondary">
                    <span>ℹ️ phpinfo()</span>
                </a>
            </div>
        </div>

        <footer>
            Beta Engine SaaS &bull; Docker Development Environment &bull; Nginx + PHP-FPM + MariaDB + Redis + RabbitMQ + PHPMyAdmin
        </footer>
    </div>
</body>
</html>
