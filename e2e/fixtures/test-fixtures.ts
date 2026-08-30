import { test as baseTest, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { HomePage } from '../pages/HomePage';
import { LoginPage } from '../pages/LoginPage';
import { RegisterPage } from '../pages/RegisterPage';
import { SearchPage } from '../pages/SearchPage';
import { ProductDetailPage } from '../pages/ProductDetailPage';
import { CartPage } from '../pages/CartPage';
import { CheckoutPage } from '../pages/CheckoutPage';
import { OrderSuccessPage } from '../pages/OrderSuccessPage';
import * as mockRoutes from '../helpers/mock-routes';

/**
 * Fixture tipada do Playwright estendida com instâncias de Page Objects,
 * helpers de mock de rede e suporte nativo a análise de acessibilidade WCAG.
 */
type CustomFixtures = {
  homePage: HomePage;
  loginPage: LoginPage;
  registerPage: RegisterPage;
  searchPage: SearchPage;
  pdpPage: ProductDetailPage;
  cartPage: CartPage;
  checkoutPage: CheckoutPage;
  orderSuccessPage: OrderSuccessPage;
  makeAxeBuilder: () => AxeBuilder;
  mocks: typeof mockRoutes;
};

export const test = baseTest.extend<CustomFixtures>({
  homePage: async ({ page }, use) => {
    await use(new HomePage(page));
  },
  loginPage: async ({ page }, use) => {
    await use(new LoginPage(page));
  },
  registerPage: async ({ page }, use) => {
    await use(new RegisterPage(page));
  },
  searchPage: async ({ page }, use) => {
    await use(new SearchPage(page));
  },
  pdpPage: async ({ page }, use) => {
    await use(new ProductDetailPage(page));
  },
  cartPage: async ({ page }, use) => {
    await use(new CartPage(page));
  },
  checkoutPage: async ({ page }, use) => {
    await use(new CheckoutPage(page));
  },
  orderSuccessPage: async ({ page }, use) => {
    await use(new OrderSuccessPage(page));
  },
  makeAxeBuilder: async ({ page }, use) => {
    const makeAxe = () => new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']);
    await use(makeAxe);
  },
  mocks: async ({}, use) => {
    await use(mockRoutes);
  },
});

export { expect };
