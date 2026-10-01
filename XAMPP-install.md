# Configurar o APP para que o localhost aponte diretamente para a pasta public_html

Aqui está o passo a passo simples para fazer essa alteração no Windows:
## 1. Localize o arquivo httpd.conf
Abra o painel de controle do XAMPP e, na linha do Apache, clique no botão Config e selecione Apache (httpd.conf).
(Se preferir ir direto pela pasta, o arquivo fica em: C:\xampp\apache\conf\httpd.conf).
## 2. Edite os caminhos do diretório
Com o arquivo aberto em um editor de texto (como o Bloco de Notas), use o atalho Ctrl + F para buscar pelo termo DocumentRoot.
Você encontrará duas linhas parecidas com estas:

DocumentRoot "C:/xampp/htdocs"
<Directory "C:/xampp/htdocs">

Altere o final do caminho de ambas as linhas para a sua pasta public_html. Por exemplo, se a sua pasta está dentro de htdocs, mude para:

DocumentRoot "C:/xampp/htdocs/public_html"
<Directory "C:/xampp/htdocs/public_html">

(Nota: Certifique-se de usar barras normais / no caminho do arquivo, mesmo no Windows).
## 3. Salve e reinicie o Apache

   1. Salve o arquivo (Ctrl + S).
   2. Vá até o painel do XAMPP.
   3. Se o Apache estiver rodando, clique em Stop e, depois, em Start para aplicar as novas configurações.

Pronto! Ao acessar http://localhost no seu navegador, ele já lerá os arquivos diretamente de dentro da sua pasta public_html. 
Deseja aplicar essa alteração de forma global para todo o localhost ou prefere criar um Virtual Host (domínio virtual como meusite.local) para manter a pasta padrão funcionando de forma independente?


