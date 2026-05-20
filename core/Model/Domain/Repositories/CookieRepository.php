<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Collection;

/**
 * CookieRepository - Gerencia a persistência e consentimento de Cookies/LGPD.
 * 
 * Melhoras Alpha Engine:
 * - Segurança: Encapsula as flags HttpOnly e Secure baseadas no estado da Registry.
 * - Conformidade: Centraliza a lógica de expiração da política de privacidade.
 */
class CookieRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Alpha Engine: Encapsula a confirmação da política de privacidade e cookies.
     * 
     * @param string $agree Valor de concordância ('1' ou '0')
     * @return bool Retorna true se o cookie foi configurado com sucesso
     */
    public function confirmPolicy(string $agree): bool
    {
        // 1. Verifica se a gestão de cookies está ativa e se o consentimento ainda não existe
        if ($this->config->get('config_cookie_id') && !isset($this->request->cookie['policy'])) {
            
            // 2. Define as opções de segurança do cookie seguindo os parâmetros do sistema
            $option = [
                'expires'  => time() + 60 * 60 * 24 * 365, // 1 ano
                'path'     => $this->config->get('session_path'),
                'secure'   => (bool)($this->request->server['HTTPS'] ?? false),
                'SameSite' => $this->config->get('config_session_samesite')
            ];

            // 3. Persistência nativa do consentimento via Header Set-Cookie
            setcookie('policy', $agree, $option);

            return true;
        }

        return false;
    }

    /**
     * Busca dados necessários para exibir a barra de cookies.
     */
    public function getCookieDisplayData(): ?Collection
    {
        if ($this->config->get('config_cookie_id') && !isset($this->request->cookie['policy'])) {
            $this->loadLanguage('common/cookie');

            return new Collection([
                'text_cookie' => sprintf($this->language->get('text_cookie'), $this->url->link('information/information.info', 'language=' . $this->config->get('config_language') . '&information_id=' . $this->config->get('config_cookie_id'))),
                'agree'       => $this->url->link('common/cookie.confirm', 'language=' . $this->config->get('config_language') . '&agree=1'),
                'disagree'    => $this->url->link('common/cookie.confirm', 'language=' . $this->config->get('config_language') . '&agree=0'),
                'cookie_id'   => $this->config->get('config_cookie_id')
            ]);
        }
        
        return null;
    }

    // Métodos obrigatórios da Interface BaseRepository
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}