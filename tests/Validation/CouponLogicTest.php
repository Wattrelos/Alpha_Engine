<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Alpha\Mappers\MapperFactory;
use Containers\AppContainer;
use Alpha\Model\Domain\Repositories\CouponRepository;
use Alpha\Model\Domain\Entities\Coupon;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CouponRepository::class)]
#[CoversClass(Coupon::class)]
class CouponLogicTest extends TestCase
{
    private CouponRepository $couponRepo;

    protected function setUp(): void
    {
        $mapperFactory = new MapperFactory();
        $container = new AppContainer();
        $this->couponRepo = new CouponRepository($mapperFactory, $container);
    }

    /**
     * Teste 1: Valida que cupons inativos (status = 0/false) são rejeitados.
     */
    public function testInactiveCouponIsInvalid(): void
    {
        $coupon = new Coupon();
        $coupon->setCode('OFF10INATIVO');
        $coupon->setStatus(false);
        $coupon->setTotal(50.0);

        $isValid = $this->couponRepo->isValid($coupon, 100.0, 1);
        $this->assertFalse($isValid, "Cupom desativado (status = false) não pode ser considerado válido.");
    }

    /**
     * Teste 2: Valida que envio de subtotal negativo ou abaixo do valor mínimo do cupom é rejeitado.
     */
    public function testSubtotalBelowMinimumThresholdIsInvalid(): void
    {
        $coupon = new Coupon();
        $coupon->setCode('MINIMO100');
        $coupon->setStatus(true);
        $coupon->setTotal(100.0); // Valor mínimo de R$ 100

        // Subtotal positivo mas menor que o mínimo R$ 100
        $this->assertFalse($this->couponRepo->isValid($coupon, 80.0, 1), "Subtotal R$ 80 < mínimo R$ 100 deve ser rejeitado.");

        // Subtotal negativo (tentativa de burlar cálculo)
        $this->assertFalse($this->couponRepo->isValid($coupon, -50.0, 1), "Subtotal negativo DEVE ser rejeitado.");
    }

    /**
     * Teste 3: Valida que cupom expirado por data fim é rejeitado.
     */
    public function testExpiredCouponIsInvalid(): void
    {
        $coupon = new Coupon();
        $coupon->setCode('EXPIRADO2020');
        $coupon->setStatus(true);
        $coupon->setTotal(10.0);
        $coupon->setDateStart('2020-01-01');
        $coupon->setDateEnd('2020-12-31');

        $this->assertFalse($this->couponRepo->isValid($coupon, 50.0, 1), "Cupom com data limite vencida deve ser rejeitado.");
    }

    /**
     * Teste 4: Valida que cupom restrito a usuários autenticados rejeita clientes não logados (customerId = 0).
     */
    public function testLoggedOnlyCouponRejectsGuestUsers(): void
    {
        $coupon = new Coupon();
        $coupon->setCode('APENAS_LOGADO');
        $coupon->setStatus(true);
        $coupon->setLogged(true);
        $coupon->setDateStart('2020-01-01');
        $coupon->setDateEnd('2099-12-31');

        $this->assertFalse($this->couponRepo->isValid($coupon, 50.0, 0), "Cupom exclusivo para clientes logados deve rejeitar visitantes sem conta (customerId = 0).");
        $this->assertTrue($this->couponRepo->isValid($coupon, 50.0, 15), "Cupom deve ser aceito se cliente estiver autenticado (customerId = 15).");
    }
}
