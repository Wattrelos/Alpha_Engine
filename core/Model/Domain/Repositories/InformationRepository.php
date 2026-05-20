<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\InformationMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Collection;

/**
 * InformationRepository - Gerencia a lógica de páginas institucionais e formulários de contato.
 * 
 * Melhoras Alpha Engine:
 * - Encapsulamento de Validação: Isola as regras de negócio do formulário de contato.
 * - Centralização de Domínio: Gerencia a exibição e integridade das páginas informativas.
 */
class InformationRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Define o Mapper principal (Catalog/Information)
     */
    protected function getMapper()
    {
        return $this->mapperFactory->get(InformationMapper::class);
    }

    /**
     * Alpha Engine: Valida os dados do formulário de contato seguindo as regras de domínio.
     * 
     * @param array $data Dados vindos da requisição POST.
     * @return array Mapa de erros de validação indexados por campo.
     */
    public function validateContactForm(array $data): array
    {
        $errors = [];

        // 1. Validação do Nome (Entre 3 e 32 caracteres)
        if (!oc_validate_length((string)($data['name'] ?? ''), 3, 32)) {
            $errors['name'] = $this->language->get('error_name');
        }

        // 2. Validação do E-mail (Formato RFC 5322)
        if (!oc_validate_email((string)($data['email'] ?? ''))) {
            $errors['email'] = $this->language->get('error_email');
        }

        // 3. Validação da Mensagem (Entre 10 e 3000 caracteres)
        if (!oc_validate_length((string)($data['enquiry'] ?? ''), 10, 3000)) {
            $errors['enquiry'] = $this->language->get('error_enquiry');
        }

        return $errors;
    }

    /**
     * Alpha Engine: Consolida os dados necessários para a página de contato.
     * 
     * @return Collection
     */
    public function getContactPageData(): Collection
    {
        $this->loadLanguage('information/contact');

        $data = [
            'store'     => $this->config->get('config_name'),
            'address'   => nl2br((string)$this->config->get('config_address')),
            'geocode'   => $this->config->get('config_geocode'),
            'telephone' => $this->config->get('config_telephone'),
            'open'      => nl2br((string)$this->config->get('config_open')),
            'comment'   => $this->config->get('config_comment'),
            'name'      => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
            'email'     => $this->customer->getEmail()
        ];

        return new Collection($data);
    }

    /**
     * Alpha Engine: Orquestra o envio do e-mail de contato seguindo as regras de domínio.
     * 
     * @param array $data
     */
    public function sendEnquiry(array $data): void
    {
        $mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'));
        $mail->setTo($this->config->get('config_email'));
        $mail->setFrom($data['email']);
        $mail->setSender(html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8'));
        $mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $data['name']), ENT_QUOTES, 'UTF-8'));
        $mail->setText(strip_tags(html_entity_decode($data['enquiry'], ENT_QUOTES, 'UTF-8')));
        $mail->send();
    }

    /**
     * Recupera os dados hidratados de uma página de informação específica.
     * 
     * @param int $informationId
     * @return Collection|null
     */
    public function getInformationData(int $informationId): ?Collection
    {
        /** @var InformationMapper $informationMapper */
        $informationMapper = $this->mapperFactory->get(InformationMapper::class);
        
        $information = $informationMapper->getInformation($informationId, $this->language_id, $this->store_id);

        if ($information) {
            return new Collection($information);
        }

        return null;
    }
}