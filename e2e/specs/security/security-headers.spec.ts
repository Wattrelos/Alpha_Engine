import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Segurança - Validação de Cabeçalhos OWASP no Navegador Real', () => {
  test('deve enviar cabeçalhos de segurança obrigatórios em respostas HTTP', async ({ page }) => {
    const response = await page.goto('/pt-br');
    expect(response).not.toBeNull();

    const headers = response!.headers();

    // X-Frame-Options (Proteção contra Clickjacking)
    expect(headers['x-frame-options']?.toLowerCase()).toBe('sameorigin');

    // X-Content-Type-Options (Proteção contra MIME-Sniffing)
    expect(headers['x-content-type-options']?.toLowerCase()).toBe('nosniff');

    // Referrer-Policy
    expect(headers['referrer-policy']).toBeTruthy();

    // Content-Security-Policy
    expect(headers['content-security-policy']).toBeTruthy();
  });

  test('deve criar cookies de sessão com proteção de caminho e isolamento', async ({ context, page }) => {
    await page.goto('/pt-br');

    const cookies = await context.cookies();
    const sessionCookie = cookies.find((c) => c.name === 'session_id');

    expect(sessionCookie).toBeDefined();
    expect(sessionCookie?.path).toBe('/');
  });
});
