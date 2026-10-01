# Erro no certificado SSL/cURL e extensões no XAMPP

### O que causava o erro?

1. **Certificado SSL / Avast (curl error 60)**:
   * O **Avast Antivirus** possui um módulo de proteção de rede (*Web Shield*) que inspeciona conexões HTTPS. Ele atua gerando certificados locais assinados pela sua própria autoridade raiz (*Avast Web/Mail Shield Root*).
   * O PHP/cURL do XAMPP utiliza um arquivo de certificados próprio (`curl-ca-bundle.crt`) e não confiava nessa autoridade raiz do Avast por padrão.

2. **Extensões do PHP pendentes**:
   * O projeto exigia as extensões `intl`, `sockets`, `zip` e `redis`. As três primeiras estavam desativadas no [php.ini](file:///C:/xampp/php/php.ini) e a DLL do Redis não vinha nativamente no XAMPP.

---

### O que foi feito para corrigir:

1. **Certificado do Avast + CA atualizada**:
   * Baixamos o pacote CA atualizado da Mozilla (`cacert.pem`).
   * Exportamos a chave raiz do Avast (*Avast Web/Mail Shield Root*) do repositório de certificados do Windows e a incluímos no bundle em `C:\xampp\apache\bin\curl-ca-bundle.crt` e `C:\xampp\php\extras\ssl\cacert.pem`.

2. **Extensões ativadas no [php.ini](file:///C:/xampp/php/php.ini)**:
   * Habilitadas: `extension=zip`, `extension=intl` e `extension=sockets`.
   * Instalada a biblioteca `php_redis.dll` (PHP 8.2 x64 TS) em `C:\xampp\php\ext\` e ativada a diretiva `extension=redis`.

3. **Execução do Composer**:
   * O comando `composer install` foi executado e concluiu com **código 0**:
     * **69 pacotes instalados com sucesso**.
     * [composer.lock](file:///c:/xampp/htdocs/backend/composer.lock) e o autoload ([vendor/autoload.php](file:///c:/xampp/htdocs/backend/vendor/autoload.php)) gerados e prontos para uso.