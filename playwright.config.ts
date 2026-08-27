import { defineConfig, devices } from '@playwright/test';
import dotenv from 'dotenv';
import path from 'path';

// Carrega variáveis de ambiente do backend ou da raiz se existirem
dotenv.config({ path: path.resolve(__dirname, 'backend/.env') });
dotenv.config({ path: path.resolve(__dirname, '.env') });

const BASE_URL = process.env.BASE_URL || 'http://localhost';

/**
 * Configuração do Playwright Test para a Alpha Engine (agsonhos).
 * Complementa a suíte de testes BDD (Gherkin/Behat) e Unitários/Integração (PHPUnit).
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './e2e/specs',
  
  /* Executa testes em paralelo para máxima velocidade */
  fullyParallel: true,
  
  /* Impede o commit acidental de test.only no CI */
  forbidOnly: !!process.env.CI,
  
  /* Número de tentativas em caso de falha */
  retries: process.env.CI ? 2 : 0,
  
  /* Quantidade de workers paralelos */
  workers: process.env.CI ? 2 : undefined,
  
  /* Timeout individual por teste (30 segundos) */
  timeout: 30000,
  
  /* Timeout e configurações para asserções expect() */
  expect: {
    timeout: 5000,
    toHaveScreenshot: {
      maxDiffPixelRatio: 0.05,
      animations: 'disabled',
    },
  },

  /* Relatórios gerados */
  reporter: [
    ['list'],
    ['html', { open: 'never', outputFolder: 'playwright-report' }],
    ['json', { outputFile: 'test-results/playwright-results.json' }],
  ],

  /* Configurações compartilhadas para todos os projetos/navegadores */
  use: {
    /* URL base da aplicação web */
    baseURL: BASE_URL,

    /* Coleta de traces em falhas ou retentativas */
    trace: 'on-first-retry',

    /* Captura screenshot em falhas */
    screenshot: 'only-on-failure',

    /* Gravação de vídeo das execuções que falharem */
    video: 'retain-on-failure',

    /* Localização e fuso horário brasileiro */
    locale: 'pt-BR',
    timezoneId: 'America/Sao_Paulo',

    /* Ignora erros de certificado HTTPS em desenvolvimento local */
    ignoreHTTPSErrors: true,
  },

  /* Configuração dos navegadores e dispositivos */
  projects: [
    /* ── Desktop Browsers ── */
    {
      name: 'chromium',
      use: { 
        ...devices['Desktop Chrome'],
        viewport: { width: 1440, height: 900 },
      },
    },

    {
      name: 'firefox',
      use: { 
        ...devices['Desktop Firefox'],
        viewport: { width: 1440, height: 900 },
      },
    },

    {
      name: 'webkit',
      use: { 
        ...devices['Desktop Safari'],
        viewport: { width: 1440, height: 900 },
      },
    },

    /* ── Mobile Devices ── */
    {
      name: 'mobile-chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'mobile-safari',
      use: { ...devices['iPhone 12'] },
    },
  ],

  /* Configuração de saída de artefatos de teste */
  outputDir: 'test-results/',
});
