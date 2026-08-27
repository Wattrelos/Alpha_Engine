import { Page, Route } from '@playwright/test';

/**
 * Estrutura padrão da resposta do ViaCEP
 */
export interface ViaCepResponse {
  cep: string;
  logradouro: string;
  complemento: string;
  bairro: string;
  localidade: string;
  uf: string;
  ibge?: string;
  gia?: string;
  ddd?: string;
  siafi?: string;
  erro?: boolean | string;
}

/**
 * Payload padrão mockado para testes rápidos e isolados
 */
export const DEFAULT_MOCK_ADDRESS: ViaCepResponse = {
  cep: '01001-000',
  logradouro: 'Praça da Sé',
  complemento: 'lado ímpar',
  bairro: 'Sé',
  localidade: 'São Paulo',
  uf: 'SP',
  ibge: '3550308',
  gia: '1004',
  ddd: '11',
  siafi: '7107',
};

/**
 * Intercepta chamadas para o serviço ViaCEP simulando resposta de sucesso imediata.
 */
export async function mockViaCepSuccess(page: Page, cep = '01001000', data: Partial<ViaCepResponse> = {}) {
  const normalizedCep = cep.replace(/\D/g, '');
  const responseData: ViaCepResponse = {
    ...DEFAULT_MOCK_ADDRESS,
    cep: `${normalizedCep.slice(0, 5)}-${normalizedCep.slice(5)}`,
    ...data,
  };

  await page.route(`**/ws/${normalizedCep}/json*`, async (route: Route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(responseData),
      headers: {
        'Access-Control-Allow-Origin': '*',
      },
    });
  });
}

/**
 * Intercepta chamadas para o serviço ViaCEP simulando CEP não localizado na base.
 */
export async function mockViaCepNotFound(page: Page, cep = '99999999') {
  const normalizedCep = cep.replace(/\D/g, '');
  await page.route(`**/ws/${normalizedCep}/json*`, async (route: Route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ erro: 'true' }),
      headers: {
        'Access-Control-Allow-Origin': '*',
      },
    });
  });
}

/**
 * Intercepta chamadas para o serviço ViaCEP simulando indisponibilidade de rede ou erro HTTP 500.
 */
export async function mockViaCepFailure(page: Page, mode: 'abort' | 'server_error' = 'server_error') {
  await page.route('**/viacep.com.br/ws/**', async (route: Route) => {
    if (mode === 'abort') {
      await route.abort('failed');
    } else {
      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ message: 'Internal Server Error' }),
        headers: {
          'Access-Control-Allow-Origin': '*',
        },
      });
    }
  });
}
