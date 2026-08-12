<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Alpha\Support\UploadSecurityHelper;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UploadSecurityHelper::class)]
class MimeTypeUploadTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir();
    }

    /**
     * Teste 1: Valida que uma imagem PNG real de alta fidelidade é aceita como segura.
     */
    public function testValidImageIsAccepted(): void
    {
        $tmpPng = tempnam($this->tempDir, 'test_valid_png_');
        $im = imagecreatetruecolor(10, 10);
        imagepng($im, $tmpPng);
        imagedestroy($im);

        $isValid = UploadSecurityHelper::isSafeImage($tmpPng, 'avatar.png');
        @unlink($tmpPng);

        $this->assertTrue($isValid, "Imagem PNG com cabeçalho de bytes reais válido deve ser aceita.");
    }

    /**
     * Teste 2: Valida que script PHP disfarçado com extensão .jpg (fake image / RCE attempt) é REJEITADO
     * pela inspeção de bytes mágicos e MIME-type real.
     */
    public function testFakeJpgDisguisedPhpScriptIsRejected(): void
    {
        $tmpFakeJpg = tempnam($this->tempDir, 'fake_jpg_');
        file_put_contents($tmpFakeJpg, '<?php echo "EXPLOIT_EXECUTADO_RCE"; ?>');

        $isValid = UploadSecurityHelper::isSafeImage($tmpFakeJpg, 'foto_perfil.jpg');
        @unlink($tmpFakeJpg);

        $this->assertFalse($isValid, "Script PHP disfarçado de imagem JPG deve ser rejeitado pelo leitor de MIME-type real.");
    }

    /**
     * Teste 3: Valida que ataque de dupla extensão (ex: shell.php.jpg) é REJEITADO.
     */
    public function testDoubleExtensionAttackIsRejected(): void
    {
        $tmpDoubleExt = tempnam($this->tempDir, 'double_ext_');
        file_put_contents($tmpDoubleExt, '<?php system($_GET["cmd"]); ?>');

        $isValid = UploadSecurityHelper::isSafeImage($tmpDoubleExt, 'shell.php.jpg');
        @unlink($tmpDoubleExt);

        $this->assertFalse($isValid, "Ataque de dupla extensão (shell.php.jpg) deve ser bloqueado.");
    }

    /**
     * Teste 4: Valida a sanitização de nomes de arquivo e mitigação de Path Traversal.
     */
    public function testPathTraversalFilenameSanitizing(): void
    {
        $maliciousFilename = "../../etc/passwd\0shell.php";
        $cleanFilename = UploadSecurityHelper::sanitizeFilename($maliciousFilename);

        $this->assertStringNotContainsString('..', $cleanFilename, "Nome sanitizado não pode conter '..' (Path Traversal).");
        $this->assertStringNotContainsString("\0", $cleanFilename, "Nome sanitizado não pode conter null bytes.");
    }
}
