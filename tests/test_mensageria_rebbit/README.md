# Testes do consumidor de eventos RabbitMQ

- Este teste visa verificar se a mensageria RebbitMQ está instalada e funcionando e se os consumidores de eventos estão funcionando corretamente.

## Como executar os testes

```bash
vendor/bin/phpunit --testdox tests/test_mensageria_rebbit
```

## Como criar um teste

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class TesteExemplo extends TestCase
{
    public function testExemplo(): void
    {
        $this->assertTrue(true);
    }
}
```
