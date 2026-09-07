# Configuração do Nginx

Migração da configuração do Apache para o Nginx

Aqui está o arquivo de configuração para o Nginx (/etc/nginx/sites-available/agsonhos):

```nginx
server {
    listen 80;
    server_name agsonhos.local www.agsonhos.local localhost;

    # Ponto de entrada do projeto (pasta pública)
    root /var/www/html/agsonhos/public_html;
    
    # Arquivos padrão
    index index.php index.html index.htm;

    # Limite para upload de imagens/arquivos
    client_max_body_size 64M;

    # Logs
    access_log /var/log/nginx/agsonhos-access.log;
    error_log /var/log/nginx/agsonhos-error.log;

    # Bloqueia acesso direto à rota /admin (pois o admin usa pasta ofuscada)
    location ^~ /admin {
        return 403;
    }

    # Roteamento do Slim Framework (envia o que não for estático para o index.php)
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Processamento PHP via PHP-FPM 8.4
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    # Bloqueio de arquivos e diretórios ocultos (.env, .git, etc.)
    location ~ /\. {
        deny all;
    }

    # Cache de arquivos estáticos (CSS, JS, imagens, fontes)
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|webp|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        try_files $uri =404;
    }
}
```

## O que mudou na conversão?

* DocumentRoot virou root: Aponta exatamente para o mesmo diretório público.
* AllowOverride All virou try_files: O Nginx não lê arquivos .htaccess. Toda regra de reescrita que antes ficava oculta no .htaccess agora precisa ser tratada diretamente pelo Nginx através da diretiva try_files $uri $uri/ /index.php?$query_string;.
* Options Indexes (Listagem de arquivos): Por padrão, o Nginx desativa a listagem de arquivos por segurança (retorna erro 403 se não houver um index.html ou index.php). Se você realmente precisar listar os arquivos no navegador como o Apache fazia, adicione a linha autoindex on; dentro do bloco location /.

Esse seu projeto utiliza PHP (como WordPress, Laravel ou PHP puro)? Se sim, me avise para ajustarmos a versão exata do PHP-FPM no bloco de configuração para que o código execute corretamente no Nginx.

