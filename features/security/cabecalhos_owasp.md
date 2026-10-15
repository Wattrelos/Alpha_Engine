# security/cabecalhos_owasp.md

> Para aplicar segurança e mitigações baseadas no OWASP Top 10 em testes automatizados com Behat e Mink, o foco muda drasticamente: em vez de testar apenas o "caminho feliz" (comportamento esperado), passamos a testar cenários de abuso e rejeição, simulando as ações de um atacante na aplicação PHP.
Abaixo estão os principais pontos do OWASP vulneráveis na camada web e como estruturar cenários Gherkin para garantir que suas mitigações estejam funcionando.
------------------------------
## 1. Injeção (A03:2021-Injection / ex: SQLi, XSS)
A principal mitigação contra injeção é garantir que dados maliciosos sejam sanitizados, rejeitados ou escapados na tela.

* Cenário de mitigação contra XSS (Cross-Site Scripting): Garantir que tags HTML injetadas por usuários sejam exibidas como texto puro (escapadas) e não executadas pelo navegador.

Cenário: Bloquear execução de XSS malicioso no perfil do usuário
  Dado que eu estou logado na minha conta
  E eu logo na página de edição de perfil
  Quando eu preencho o campo "Nome" com "<script>alert('hack')</script>"
  E eu clico em "Salvar"
  Então eu não devo ver um alerta Javascript contendo "hack"
  E eu devo ver o texto "<script>alert('hack')</script>" renderizado textualmente

## 2. Quebra de Autenticação (A07:2021-Identification and Authentication Failures)
Mitigações comuns envolvem políticas estritas de senha, bloqueio de contas por tentativas repetidas (Rate Limiting) e invalidação correta de sessões.

* Cenário de mitigação contra Brute Force / Credential Stuffing: Garantir que o sistema bloqueie acessos após sucessivas falhas.

Cenário: Bloqueio de conta após tentativas excessivas de login inválido
  Dado que eu estou na página de login
  Quando eu tento logar com a senha errada por 5 vezes consecutivas
  Então a minha conta deve ser temporariamente bloqueada
  E eu devo ver a mensagem "Muitas tentativas. Tente novamente em 15 minutos."

## 3. Falha de Controle de Acesso (A01:2021-Broken Access Control)
Usuários não podem acessar URLs ou recursos de outros usuários alterando IDs na barra de endereço (IDOR - Insecure Direct Object Reference) ou acessando endpoints administrativos sem privilégios.

* Cenário de mitigação contra IDOR / Quebra de Privilégio:

Cenário: Usuário comum não pode acessar o painel administrativo diretamente pela URL
  Dado que eu estou logado como um usuário do tipo "Cliente"
  Quando eu tento acessar explicitamente a URL "/admin/dashboard"
  Então o código de resposta do servidor deve ser 403
  E eu devo ver a mensagem "Acesso Negado"

## 4. Exposição de Dados Sensíveis (A02:2021-Cryptographic Failures)
O sistema não deve vazar dados como senhas, tokens ou dados de cartão de crédito em texto limpo nos formulários ou respostas HTTP.

* Cenário de mitigação de conformidade visual:

Cenário: Mascaramento de dados confidenciais na interface
  Dado que eu acessei a minha carteira virtual
  Então o campo "Número do Cartão" deve exibir apenas os 4 últimos dígitos "**** **** **** 1234"
  E o campo "Senha Atual" deve ocultar os caracteres digitados

------------------------------
## Como implementar isso no PHP (FeatureContext.php)
Para validar códigos de status HTTP (como o 403 ou 404) ou lidar com pop-ups do sistema utilizando o Mink, você utilizará asserções específicas no arquivo de contexto do Behat:

use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext
{
    /**
     * @Then o código de resposta do servidor deve ser :code
     */
    public function oCodigoDeRespostaDoServidorDeveSer($code)
    {
        $this->assertSession()->statusCodeEquals((int)$code);
    }

    /**
     * @Then eu não devo ver um alerta Javascript contendo :text
     */
    public function euNaoDevoVerUmAlertaJavascriptContendo($text)
    {
        // Requer um Driver que suporte Javascript (ex: Selenium2 ou Panther)
        try {
            $alertText = $this->getSession()->getDriver()->getWebDriverSession()->getAlertText();
            if (strpos($alertText, $text) !== false) {
                throw new \Exception("Alerta indesejado encontrado com o texto: " . $alertText);
            }
        } catch (\Exception $e) {
            // Se não houver alerta, o teste passa com segurança
        }
    }
}








